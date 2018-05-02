#!/usr/bin/env tarantool

box.cfg {
    listen = 3301,
    log_level = 5
}

if jobserver ~= nil then
    -- hot code reload using tarantoolctl or dofile()

    -- unload old application
    jobserver.stop()
    -- clear cache for loaded modules and dependencies
    package.loaded['jobserver'] = nil
end

local config = require('jobserver_config')

-- ensure a user exists
if not box.schema.user.exists(config.user) then
    box.schema.user.create(config.user, {password = config.password})
    box.schema.user.grant(config.user, 'read,write,execute', 'universe', nil)
end

-- load a new version of app and all dependencies
jobserver = require('jobserver')
jobserver.start(config)

-- start monitoring
if config.monitor.host then
    require('monitor').monitor(
        config.monitor.host,
        config.monitor.port,
        jobserver.queue,
        config.monitor.scrape_interval
    )
end
