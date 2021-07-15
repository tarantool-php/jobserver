local prom = require('prometheus')
local http = require('http.server')
local fiber = require('fiber')

local function worker(queue, scrape_interval)
    local metrics = {}

    for tube in pairs(queue.stats()) do
        metrics[tube] = {
            task_ready = prom.gauge(
                "jobserver_" .. tube .. "_task_ready",
                "Number of ready tasks"
            ),
            task_taken = prom.gauge(
                "jobserver_" .. tube .. "_task_taken",
                "Number of taken tasks"
            ),
            task_done = prom.gauge(
                "jobserver_" .. tube .. "_task_done",
                "Number of done tasks"
            ),
            task_buried = prom.gauge(
                "jobserver_" .. tube .. "_task_buried",
                "Number of buried tasks"
            ),
            task_delayed = prom.gauge(
                "jobserver_" .. tube .. "_task_delayed",
                "Number of delayed tasks"
            ),
            task_total = prom.gauge(
                "jobserver_" .. tube .. "_task_total",
                "Total number of tasks"
            )
        }
    end

    local stats
    while true do
        stats = queue.stats()

        for tube, metric in pairs(metrics) do
            metric.task_ready:set(stats[tube].tasks.ready, {tube})
            metric.task_taken:set(stats[tube].tasks.taken, {tube})
            metric.task_done:set(stats[tube].tasks.done, {tube})
            metric.task_buried:set(stats[tube].tasks.buried, {tube})
            metric.task_delayed:set(stats[tube].tasks.delayed, {tube})
            metric.task_total:set(stats[tube].tasks.total, {tube})
        end

        fiber.sleep(scrape_interval)
    end
end

local function monitor(host, port, queue, scrape_interval)
    local httpd = http.new(host, port)

    httpd:route({ path = '/metrics' }, prom.collect_http)
    httpd:start()

    fiber.create(worker, queue, scrape_interval)
end

return {
    monitor = monitor
}
