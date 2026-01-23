#!/bin/bash
###############################################################################
# 重启异步数据迁移守护进程
# 使用方法：bash restart_async.sh
###############################################################################

echo "========================================"
echo "重启异步数据迁移守护进程"
echo "========================================"

# 查找并停止旧进程
echo "1. 查找旧进程..."
PIDS=$(ps aux | grep 'AsyncDataMigration process' | grep -v grep | awk '{print $2}')

if [ -z "$PIDS" ]; then
    echo "   未找到运行中的进程"
else
    echo "   找到进程: $PIDS"
    echo "2. 停止旧进程..."
    for PID in $PIDS; do
        echo "   杀死进程 $PID"
        kill -9 $PID
    done
    sleep 2
fi

# 启动新进程
echo "3. 启动新进程..."
CMDDIR=/var/www/bin/sa/uat
LOGDIR=/var/log/dms/sa/uat
mkdir -p $LOGDIR
LOGFILE=$(date +"%Y%m%d")
LOGFILE_LOC=$LOGDIR/async_migration_$LOGFILE.log

nohup php $CMDDIR/yii_console_sau.php AsyncDataMigration process --daemon < /dev/null >> $LOGFILE_LOC 2>&1 &

sleep 1

# 检查是否启动成功
PROCS_CHECK=$(ps aux | grep 'AsyncDataMigration process' | grep -v grep)
if [ -z "$PROCS_CHECK" ]; then
    echo "   ❌ 启动失败，请检查日志: $LOGFILE_LOC"
    exit 1
else
    echo "   ✅ 启动成功"
    echo "   日志文件: $LOGFILE_LOC"
fi

echo "========================================"
echo "完成！"
echo "========================================"

