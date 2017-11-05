queue = require('queue')

local function start(config)
    box.once('jobserver:v1.0', function()
        local tube = queue.create_tube('default', 'fifottl', {if_not_exists = true})
        -- temporary disabled as seems like this method is not yet released
        -- tube:grant(config.user)

        -- tube:put({recurrence = 60, payload = {service = 'greet', args = {name = 'Foobar'}}}, {ttr = 300})
    end)
end

local function stop()
end

return {
    start = start,
    stop = stop
}
