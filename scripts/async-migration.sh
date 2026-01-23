#!/bin/bash
###############################################################################
# 异步数据迁移后台处理器管理脚本
#
# 使用方法：
#   ./scripts/async-migration.sh start    # 启动
#   ./scripts/async-migration.sh stop     # 停止
#   ./scripts/async-migration.sh restart  # 重启
#   ./scripts/async-migration.sh status   # 查看状态
#   ./scripts/async-migration.sh tail     # 查看实时日志
#
###############################################################################

# 配置参数（请根据实际情况修改）
PROJECT_DIR="/var/www/html/sa/uat"  # 项目根目录，请修改为实际路径
PHP_BIN="php"                       # PHP可执行文件路径
LOG_DIR="/var/log/dms/sa/uat"      # 日志目录
PID_FILE="/tmp/async-migration.pid"  # PID文件路径

# 命令路径
CMDDIR="/var/www/bin/sa/uat"
YIIC_CMD="${CMDDIR}/yii_console_sau.php"

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

###############################################################################
# 辅助函数
###############################################################################

# 输出信息
info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

# 输出警告
warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

# 输出错误
error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查项目目录是否存在
check_project_dir() {
    if [ ! -d "$PROJECT_DIR" ]; then
        error "项目目录不存在: $PROJECT_DIR"
        error "请修改脚本中的 PROJECT_DIR 变量"
        exit 1
    fi

    if [ ! -f "$YIIC_CMD" ]; then
        error "Yii命令文件不存在: $YIIC_CMD"
        exit 1
    fi
}

# 检查PHP是否可用
check_php() {
    if ! command -v $PHP_BIN &> /dev/null; then
        error "PHP未找到: $PHP_BIN"
        error "请修改脚本中的 PHP_BIN 变量"
        exit 1
    fi
}

# 检查守护脚本是否运行
is_daemon_running() {
    DAEMON_PROCS=`ps aux | grep 'runAsyncMigration' | grep -v grep`
    if [ ! -z "$DAEMON_PROCS" ]; then
        return 0  # 运行中
    fi
    return 1  # 未运行
}

# 检查处理器是否运行
is_worker_running() {
    WORKER_PROCS=`ps aux | grep 'yii_console_sau.php AsyncDataMigration process' | grep -v grep`
    if [ ! -z "$WORKER_PROCS" ]; then
        return 0  # 运行中
    fi
    return 1  # 未运行
}

###############################################################################
# 主要功能
###############################################################################

# 启动服务（启动守护脚本）
start() {
    info "正在启动异步数据迁移处理器..."

    check_project_dir
    check_php

    # 检查守护脚本是否已经运行
    DAEMON_PROCS=`ps aux | grep 'runAsyncMigration' | grep -v grep`
    if [ ! -z "$DAEMON_PROCS" ]; then
        warn "守护脚本已经在运行中"
        echo "$DAEMON_PROCS"
        return 0
    fi

    # 确保日志目录存在
    mkdir -p "$LOG_DIR"

    # 获取脚本目录
    SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    DAEMON_SCRIPT="${SCRIPT_DIR}/runAsyncMigration"

    # 检查守护脚本是否存在
    if [ ! -f "$DAEMON_SCRIPT" ]; then
        error "守护脚本不存在: $DAEMON_SCRIPT"
        return 1
    fi

    # 确保脚本有执行权限
    chmod +x "$DAEMON_SCRIPT"

    # 启动守护脚本
    nohup "$DAEMON_SCRIPT" > /dev/null 2>&1 &

    # 保存守护脚本PID
    echo $! > "$PID_FILE"

    # 等待2秒检查是否启动成功
    sleep 2

    # 检查守护脚本是否运行
    if ps -p $(cat "$PID_FILE") > /dev/null 2>&1; then
        info "守护脚本启动成功！PID: $(cat $PID_FILE)"

        # 检查处理器是否运行
        WORKER_PROCS=`ps aux | grep 'yii_console_sau.php AsyncDataMigration process' | grep -v grep`
        if [ ! -z "$WORKER_PROCS" ]; then
            info "异步处理器已启动"
        else
            warn "守护脚本已启动，等待处理器启动..."
        fi

        LOGFILE=`date +"%Y%m%d"`
        LOGFILE_LOC="${LOG_DIR}/async_migration_${LOGFILE}.log"
        info "日志文件: $LOGFILE_LOC"
        return 0
    else
        error "启动失败！"
        return 1
    fi
}

# 停止服务
stop() {
    info "正在停止异步数据迁移处理器..."

    # 1. 停止守护脚本
    DAEMON_PROCS=`ps aux | grep 'runAsyncMigration' | grep -v grep | awk '{print $2}'`
    if [ ! -z "$DAEMON_PROCS" ]; then
        info "停止守护脚本..."
        for pid in $DAEMON_PROCS; do
            kill -TERM $pid 2>/dev/null
        done
        sleep 1
    fi

    # 2. 停止异步处理器
    WORKER_PROCS=`ps aux | grep 'yii_console_sau.php AsyncDataMigration process' | grep -v grep | awk '{print $2}'`
    if [ ! -z "$WORKER_PROCS" ]; then
        info "停止异步处理器..."
        for pid in $WORKER_PROCS; do
            kill -TERM $pid 2>/dev/null
        done

        # 等待进程结束（最多10秒）
        for i in {1..10}; do
            WORKER_PROCS=`ps aux | grep 'yii_console_sau.php AsyncDataMigration process' | grep -v grep`
            if [ -z "$WORKER_PROCS" ]; then
                info "停止成功"
                rm -f "$PID_FILE"
                return 0
            fi
            sleep 1
        done

        # 如果还在运行，强制终止
        WORKER_PROCS=`ps aux | grep 'yii_console_sau.php AsyncDataMigration process' | grep -v grep | awk '{print $2}'`
        if [ ! -z "$WORKER_PROCS" ]; then
            warn "进程未响应，强制终止..."
            for pid in $WORKER_PROCS; do
                kill -9 $pid 2>/dev/null
            done
            sleep 1
        fi
    fi

    # 清理PID文件
    rm -f "$PID_FILE"

    # 最终检查
    DAEMON_PROCS=`ps aux | grep 'runAsyncMigration' | grep -v grep`
    WORKER_PROCS=`ps aux | grep 'yii_console_sau.php AsyncDataMigration process' | grep -v grep`

    if [ -z "$DAEMON_PROCS" ] && [ -z "$WORKER_PROCS" ]; then
        info "所有进程已停止"
        return 0
    else
        warn "部分进程可能仍在运行，请手动检查"
        return 1
    fi
}

# 重启服务
restart() {
    info "正在重启异步数据迁移处理器..."
    stop
    sleep 2
    start
}

# 查看状态
status() {
    echo "========================================="
    echo "异步数据迁移处理器状态"
    echo "========================================="

    # 获取当天日志文件
    LOGFILE=`date +"%Y%m%d"`
    LOGFILE_LOC="${LOG_DIR}/async_migration_${LOGFILE}.log"

    # 检查守护脚本
    echo ""
    echo "【守护脚本】"
    DAEMON_PROCS=`ps aux | grep 'runAsyncMigration' | grep -v grep`
    if [ ! -z "$DAEMON_PROCS" ]; then
        echo -e "状态: ${GREEN}运行中${NC}"
        echo "$DAEMON_PROCS" | awk '{printf "  PID: %s, CPU: %s%%, MEM: %s%%, 运行时间: %s\n", $2, $3, $4, $10}'
    else
        echo -e "状态: ${RED}未运行${NC}"
    fi

    # 检查异步处理器
    echo ""
    echo "【异步处理器】"
    WORKER_PROCS=`ps aux | grep 'yii_console_sau.php AsyncDataMigration process' | grep -v grep`
    if [ ! -z "$WORKER_PROCS" ]; then
        echo -e "状态: ${GREEN}运行中${NC}"
        echo "$WORKER_PROCS" | awk '{printf "  PID: %s, CPU: %s%%, MEM: %s%%, 运行时间: %s\n", $2, $3, $4, $10}'
    else
        echo -e "状态: ${RED}未运行${NC}"
    fi

    # 显示最近的日志
    echo ""
    echo "【最近10条日志】"
    echo "----------------------------------------"
    if [ -f "$LOGFILE_LOC" ]; then
        tail -n 10 "$LOGFILE_LOC"
    else
        echo "今日日志文件不存在: $LOGFILE_LOC"
    fi

    echo "========================================="
    echo "日志目录: $LOG_DIR"
    echo "今日日志: $LOGFILE_LOC"
    echo "========================================="
}

# 实时查看日志
tail_log() {
    # 获取当天日志文件
    LOGFILE=`date +"%Y%m%d"`
    LOGFILE_LOC="${LOG_DIR}/async_migration_${LOGFILE}.log"

    if [ ! -f "$LOGFILE_LOC" ]; then
        error "今日日志文件不存在: $LOGFILE_LOC"
        echo ""
        echo "历史日志文件列表："
        ls -lh "${LOG_DIR}"/async_migration_*.log 2>/dev/null || echo "无历史日志"
        exit 1
    fi

    info "实时查看今日日志 (Ctrl+C 退出)"
    info "日志文件: $LOGFILE_LOC"
    tail -f "$LOGFILE_LOC"
}

# 查看历史日志
tail_history() {
    echo "历史日志文件列表："
    echo "----------------------------------------"
    ls -lh "${LOG_DIR}"/async_migration_*.log 2>/dev/null

    if [ $? -ne 0 ]; then
        error "没有找到历史日志文件"
        exit 1
    fi

    echo ""
    read -p "请输入要查看的日志日期 (格式: YYYYMMDD): " LOG_DATE

    LOGFILE_LOC="${LOG_DIR}/async_migration_${LOG_DATE}.log"

    if [ ! -f "$LOGFILE_LOC" ]; then
        error "日志文件不存在: $LOGFILE_LOC"
        exit 1
    fi

    info "查看日志: $LOGFILE_LOC"
    tail -f "$LOGFILE_LOC"
}

###############################################################################
# 主程序
###############################################################################

case "$1" in
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    status)
        status
        ;;
    tail)
        tail_log
        ;;
    history)
        tail_history
        ;;
    *)
        echo "异步数据迁移处理器管理脚本"
        echo ""
        echo "使用方法: $0 {start|stop|restart|status|tail|history}"
        echo ""
        echo "命令说明："
        echo "  start   - 启动后台处理器"
        echo "  stop    - 停止后台处理器"
        echo "  restart - 重启后台处理器"
        echo "  status  - 查看运行状态"
        echo "  tail    - 实时查看今日日志"
        echo "  history - 查看历史日志"
        echo ""
        exit 1
        ;;
esac

exit $?

