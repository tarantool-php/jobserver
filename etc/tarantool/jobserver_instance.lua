#!/usr/bin/env tarantool

box.cfg {
    -- listen = os.getenv('LISTEN_URI'):gsub('^unix://', '', 1):gsub('^unix/:', '', 1):gsub('^tcp://', '', 1),
    listen = 3301,
    log_level = 5
}

-- allow group users to access the socket
if '/' == string.sub(box.cfg.listen, 1, 1) then
    require('fio').chmod(box.cfg.listen, tonumber('0664', 8))
end

if jobserver ~= nil then
    -- hot code reload using tarantoolctl or dofile()

    -- unload old application
    jobserver.stop()
    -- clear cache for loaded modules and dependencies
    package.loaded['jobserver'] = nil
end

local config = require('jobserver_config')

-- ensure a user exists
if config.user then
    box.schema.user.create(config.user, {
        if_not_exists = true,
        password = config.password
    })
end

-- load a new version of app and all dependencies
jobserver = require('jobserver')
jobserver.start(config)

-- start monitoring
if config.monitor then
    require('monitor').monitor(
        config.monitor.host,
        config.monitor.port,
        jobserver.queue,
        config.monitor.scrape_interval
    )
end
