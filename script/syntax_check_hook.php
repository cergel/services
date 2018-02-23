#!/usr/bin/env php
<?php
/***
 * 本地检查即将提交的php文件有没有语法错误
 * 使用方法
 * 将.git/hooks/prepare-commit-msg 软链至本文件
 * 给予本文件可执行权限
 */
$diff_shell = shell_exec("git diff --cached --name-only");
$target_files = [];
$file_filter = '@\S*.php@';
preg_match_all($file_filter, $diff_shell, $target_files);
if (!empty($target_files[0])) {
    $all_php_file = $target_files[0];
} else {
    $all_php_file = [];
}
$err_php_files = [];
$num = count($all_php_file);
$now = 1;
foreach ($all_php_file as $need_check_file) {
    //如果是删除文件的话，则不进行语法检查
    if (!file_exists($need_check_file)) {
        continue;
    }

    $source_code = file_get_contents($need_check_file);



    $result = shell_exec("php -l " . $need_check_file);
    if (stristr($result, "No syntax errors detected") === false) {
        $err_php_files[] = $need_check_file;
    }
    $process = (round($now / $num, 4) * 100) . " %";
    echo "已经处理 {$process}\r";
    $now++;
}
system('clear');
if ($err_php_files) {
    echo "\033[01;31m 以下文件存在语法错误，请检查后提交 \033[0m" . PHP_EOL;
    foreach ($err_php_files as $value) {
        echo $value . PHP_EOL;
    }
    echo "failed" . PHP_EOL;
    exit(1);
} else {
    echo "\e[01;31m 文件检查完毕，无语法错误 \e[0m" . PHP_EOL;
    echo "success" . PHP_EOL;
    exit(0);
}
