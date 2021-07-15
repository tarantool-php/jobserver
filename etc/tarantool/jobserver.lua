queue = require('queue')

local function put_many(tube, items)
    local put = {}

    box.begin()
    for k, item in pairs(items) do
        put[k] = tube:put(unpack(item))
    end
    box.commit()

    return put
end

-- https://github.com/tarantool/queue/commit/0dfecdfdb971aa440adfb3c0f3b06e1190ec1585

local function grant(tube, user)
    local function tube_grant_space(user, name, tp)
        box.schema.user.grant(user, tp or 'read,write', 'space', name, {
            if_not_exists = true,
        })
    end

    local function tube_grant_func(user, name)
        box.schema.func.create(name, { if_not_exists = true })
        box.schema.user.grant(user, 'execute', 'function', name, {
            if_not_exists = true
        })
    end

    tube_grant_space(user, '_queue', 'read')
    tube_grant_space(user, '_queue_consumers')
    tube_grant_space(user, '_queue_taken')
    tube_grant_space(user, tube.name)

    local prefix = 'queue.tube.' .. tube.name
    tube_grant_func(user, prefix .. ':put')
    tube_grant_func(user, prefix .. ':put_many')
    tube_grant_func(user, prefix .. ':take')
    tube_grant_func(user, prefix .. ':touch')
    tube_grant_func(user, prefix .. ':ack')
    tube_grant_func(user, prefix .. ':release')
    tube_grant_func(user, prefix .. ':peek')
    tube_grant_func(user, prefix .. ':bury')
    tube_grant_func(user, prefix .. ':kick')
    tube_grant_func(user, prefix .. ':delete')
    tube_grant_func(user, prefix .. ':truncate')
    tube_grant_func(user, 'queue.stats')
    tube_grant_func(user, 'queue.statistics')
end

local function start(config)
    local tube = queue.create_tube('default', 'fifottl', { if_not_exists = true })
    local user = config.user or 'guest'

    tube.put_many = put_many
    grant(tube, user)

    -- tube:put({recurrence = 60, payload = {service = 'greet', args = {name = 'Foobar'}}}, {ttr = 300})
end

local function stop()
end

return {
    start = start,
    stop = stop,
    queue = queue
}
