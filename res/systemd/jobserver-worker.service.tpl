[Unit]
Description=Job Server Worker (${APP_TNT_QUEUE})

[Service]
EnvironmentFile=/etc/default/jobserver-worker
ExecStart=${APP_RELEASE_DIR}/vendor/bin/jobqueue run "${APP_TNT_QUEUE}" --host "${TNT_JOBQUEUE_HOST}" --config ${APP_RELEASE_DIR}/app/config/jobqueue.php --executors-config ${APP_RELEASE_DIR}/app/config/executors.php
Restart=always

[Install]
WantedBy=multi-user.target
