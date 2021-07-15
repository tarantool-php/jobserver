<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;

final class Kernel
{
    private const ENV_ENV = 'TNT_JOBSERVER_ENV';
    private const ENV_DEBUG = 'TNT_JOBSERVER_DEBUG';
    private const CONFIG_EXTS = '.{yaml,yml}';

    private $environment;
    private $debug;
    private $container;
    private $rootDir;

    public function __construct(string $environment, bool $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
    }

    public static function fromEnv() : self
    {
        $environment = ($_SERVER[self::ENV_ENV] ?? $_ENV[self::ENV_ENV] ?? null) ?: 'dev';

        $debug = $_SERVER[self::ENV_DEBUG] ?? $_ENV[self::ENV_DEBUG] ?? 'prod' !== $environment;
        $debug = (int) $debug || \filter_var($debug, \FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

        return new self($environment, (bool) $debug);
    }

    public function getEnvironment() : string
    {
        return $this->environment;
    }

    public function isDebug() : bool
    {
        return $this->debug;
    }

    public function getRootDir() : string
    {
        if (null === $this->rootDir) {
            $r = new \ReflectionObject($this);
            $this->rootDir = \dirname($r->getFileName(), 2);
        }

        return $this->rootDir;
    }

    public function getContainer() : Container
    {
        if (!$this->container) {
            $this->initializeContainer();
        }

        return $this->container;
    }

    public function getCacheDir() : string
    {
        return $this->getRootDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir() : string
    {
        return $this->getRootDir().'/var/log';
    }

    private function getContainerClass() : string
    {
        return \str_replace('\\', '_', self::class).\ucfirst($this->environment).($this->debug ? 'Debug' : '').'Container';
    }

    private function initializeContainer() : void
    {
        $class = $this->getContainerClass();
        $cacheDir = $this->getCacheDir();
        $cache = new ConfigCache($cacheDir.'/'.$class.'.php', $this->debug);
        $oldContainer = null;
        if ($fresh = $cache->isFresh()) {
            // Silence E_WARNING to ignore "include" failures - don't use "@" to prevent silencing fatal errors
            $errorLevel = \error_reporting(\E_ALL ^ \E_WARNING);
            $fresh = $oldContainer = false;
            try {
                if (\file_exists($cache->getPath()) && \is_object($this->container = include $cache->getPath())) {
                    $this->container->set('kernel', $this);
                    $oldContainer = $this->container;
                    $fresh = true;
                }
            } catch (\Throwable $e) {
            } finally {
                \error_reporting($errorLevel);
            }
        }

        if ($fresh) {
            return;
        }

        $container = $this->buildContainer();
        $container->compile();

        if (null === $oldContainer && \file_exists($cache->getPath())) {
            $errorLevel = \error_reporting(\E_ALL ^ \E_WARNING);
            try {
                $oldContainer = include $cache->getPath();
            } catch (\Throwable $e) {
            } finally {
                \error_reporting($errorLevel);
            }
        }
        $oldContainer = \is_object($oldContainer) ? new \ReflectionClass($oldContainer) : false;

        $this->dumpContainer($cache, $container, $class, 'Container');
        $this->container = require $cache->getPath();
        $this->container->set('kernel', $this);

        if ($oldContainer && \get_class($this->container) !== $oldContainer->name) {
            // Because concurrent requests might still be using them,
            // old container files are not removed immediately,
            // but on a next dump of the container.
            static $legacyContainers = [];
            $oldContainerDir = \dirname($oldContainer->getFileName());
            $legacyContainers[$oldContainerDir.'.legacy'] = true;
            foreach (\glob(\dirname($oldContainerDir).\DIRECTORY_SEPARATOR.'*.legacy') as $legacyContainer) {
                if (!isset($legacyContainers[$legacyContainer]) && @\unlink($legacyContainer)) {
                    (new Filesystem())->remove(\substr($legacyContainer, 0, -7));
                }
            }

            \touch($oldContainerDir.'.legacy');
        }
    }

    private function getKernelParameters() : array
    {
        return [
            'kernel.root_dir' => \realpath($this->getRootDir()) ?: $this->getRootDir(),
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.cache_dir' => \realpath($this->getCacheDir()) ?: $this->getCacheDir(),
            'kernel.logs_dir' => \realpath($this->getLogDir()) ?: $this->getLogDir(),
        ];
    }

    private function buildContainer() : ContainerBuilder
    {
        foreach (['cache' => $this->getCacheDir(), 'logs' => $this->getLogDir()] as $name => $dir) {
            if (!\is_dir($dir)) {
                if (false === @\mkdir($dir, 0777, true) && !\is_dir($dir)) {
                    throw new \RuntimeException(\sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!\is_writable($dir)) {
                throw new \RuntimeException(\sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }

        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);

        $this->registerContainerConfiguration($this->getContainerLoader($container));

        return $container;
    }

    private function getContainerBuilder() : ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->getParameterBag()->add($this->getKernelParameters());

        $container->registerForAutoconfiguration(Command::class)->addTag('console.command');
        $container->addCompilerPass(new AddConsoleCommandPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass($this->createCollectingCompilerPass());

        return $container;
    }

    private function getContainerLoader(ContainerInterface $container) : LoaderInterface
    {
        $locator = new FileLocator($this);
        $resolver = new LoaderResolver([
            new ClosureLoader($container),
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new class($container, $locator) extends GlobFileLoader {
                private $imported = [];

                public function import($resource, $type = null, $ignoreErrors = false, $sourceResource = null)
                {
                    if (isset($this->imported[$resource])) {
                        return null;
                    }

                    $this->imported[$resource] = true;

                    return parent::import($resource, $type, $ignoreErrors, $sourceResource);
                }
            },
        ]);

        return new DelegatingLoader($resolver);
    }

    private function registerContainerConfiguration(LoaderInterface $loader) : void
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $this->configureContainer($container, $loader);
            $container->addObjectResource($this);
        });
    }

    private function configureContainer(ContainerBuilder $container, LoaderInterface $loader) : void
    {
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getRootDir().'/config';

        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{'.$this->environment.'}/**/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{'.$this->environment.'}/**/*'.self::CONFIG_EXTS, 'glob');
    }

    private function createCollectingCompilerPass() : CompilerPassInterface
    {
        return new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $containerBuilder) : void
            {
                $appDefinition = $containerBuilder->findDefinition(Application::class);
                foreach ($containerBuilder->getDefinitions() as $definition) {
                    if (!\is_a($definition->getClass(), Command::class, true)) {
                        continue;
                    }
                    $appDefinition->addMethodCall('add', [new Reference($definition->getClass())]);
                }
            }
        };
    }

    private function dumpContainer(ConfigCache $cache, ContainerBuilder $container, string $class, string $baseClass) : void
    {
        $dumper = new PhpDumper($container);

        $content = $dumper->dump([
            'class' => $class,
            'base_class' => $baseClass,
            'file' => $cache->getPath(),
            'as_files' => true,
            'debug' => $this->debug,
            'build_time' => \time(),
        ]);

        $rootCode = \array_pop($content);
        $dir = \dirname($cache->getPath()).'/';
        $fs = new Filesystem();

        foreach ($content as $file => $code) {
            $fs->dumpFile($dir.$file, $code);
            @\chmod($dir.$file, 0666 & ~\umask());
        }
        $legacyFile = \dirname($dir.$file).'.legacy';
        if (\file_exists($legacyFile)) {
            @\unlink($legacyFile);
        }

        $cache->write($rootCode, $container->getResources());
    }
}
