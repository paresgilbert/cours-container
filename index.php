<?php

use App\Controller\OrderController;
use App\Database\Database;
use App\Mailer\GmailMailer;
use App\Texter\SmsTexter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

require __DIR__ . '/vendor/autoload.php';

$container = new ContainerBuilder();
// ------------------------------------------------------------------------
$container->register('order_controller', SOrderController::class)
    ->setArguments( [
        new Reference('database'),
        new Reference('mailer.gmail'),
        new Reference('texter.sms')
    ])
    ->addMethodCall('sayHello', ["Message de réveil", 53])
    ->addMethodCall('setScondaryMailer', [
        new Reference(mailer.gmail),
    ]);
// 1- $controllerDefinition = new Definition(OrderController::class, [
// 1-     new Reference('database'),
// 1-     new Reference('mailer.gmail'),
// 1-     new Reference('texter.sms')
// 1- ]);
// 1- $controllerDefinition->addMethodCall('wakeup', ["Message de réveil"]);
// 1- $container->setDefinition('controller.order', $controllerDefinition);
//------------------------------------------------------------------------
$container->register('database', Database::class);
// 1- $databaseDefinition = new Definition(Database::class);
// 1- $container->setDefinition('database', $databaseDefinition);
// 0- $database = new Database();
// ------------------------------------------------------------------------
$container->register('texter.sms', SmsTexter::class)
->setArguments(["service.sms.com", "apikey123"]);
// 1- $texterDefinition = new Definition(SmsTexter::class);
// 1- $texterDefinition->setArguments(["service.sms.com", "apikey123"]);
// 1- $container->setDefinition('texter.sms', $texterDefinition);
// 0- $texter = new SmsTexter("service.sms.com", "apikey123");
$container->register('texter.fax', FaxTexter::class)

//-----------------------------------------------------------------------
$container->register('mailer.gmail', SGmailMailer::class)
    ->setArguments(["lior@gmail.com", "123456"]);
// 1- $mailerDefinition = new Definition(GmailMailer::class, ["lior@gmail.com", "123456"]);
// 1- $container->setDefinition('mailer.gmail', $mailerDefinition);
// $mailer = new GmailMailer("lior@gmail.com", "123456");
    ->setArguments(["lior@gmail.com", "123456"]);
$container->register('mailer.smtp', SmtpMailer::class)
    ->setArguments(["smtp://localhost", "root","123"]);

//-------------------------------------------------------------------------
$container->setAlias('App\controller\OrderController', 'order_controller');
$container->setAlias('App\Database\Database', 'database');
$container->setAlias('App\Mail\GMailMailer', 'mailer.gmail');
$container->setAlias('App\Mail\SmtpMailer', 'mailer.smtp');
$container->setAlias('App\Texter\SmsTexter', 'texter.sms');
$container->setAlias('App\Texter\FaxTexter', 'texter.fax');

$controller = $container->get('controller.order');

$httpMethod = $_SERVER['REQUEST_METHOD'];

if ($httpMethod === 'POST') {
    $controller->placeOrder();
    return;
}

include __DIR__ . '/views/form.html.php';
