${APP_LOG_DIR}/*.log {
    daily
    missingok
    rotate 4
    size 1M
    compress
    delaycompress
    notifempty
    copytruncate
}
