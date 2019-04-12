<?php

/**
 * @cocophp 框架路径
 */
const cocophp      = '../coco/';
/**
 * @applications 项目路径
 */
const applications = '../app/';
/**
 * @union 公共配置,该目录文件配置,为全局可见性.
 */
const union        = '../public/';
/**
 * ====================================================================================
 * 从此处开始，下面常量尽量不要修改
 * ====================================================================================
 */
/**
 * @lib lib路径
 */
const lib          = 'core/';
/**
 * @autoload autoload.php文件路径
 */
const autoload     = 'autoload.php';
/**
 * @coco coco.php文件路径
 */
const coco         = 'coco.php';
/**
 * @require require class autoload;
 */
require_once cocophp.lib.autoload;
/**
 * @require require class cocophp;
 */
require_once cocophp.lib.coco;
/**
 * @autoload::loading register php autolaod;
 */
autoload::loading();
/**
 * @coco->running running,the cocophp running;
 */
echo ( new coco() )->running();
