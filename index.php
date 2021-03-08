<?php

use App\Controller\OrderController;
use App\Database\Database;
use App\Mailer\GmailMailer;
use App\Mailer\MailerInterface;
use App\Mailer\SmtpMailer;
use App\Texter\FaxTexter;
use App\Texter\SmsTexter;
use App\Texter\TexterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

require __DIR__ . '/vendor/autoload.php';

$container = new ContainerBuilder();
// ------------------------------------------------------------------------
$container->autowire('order_controller', OrderController::class)
    ->setPublic(true)
// 03- $container->register('order_controller', OrderController::class)
// 03-   ->setPublic(true)
// 03-   ->setAutowired(true)
// analyse le constructeur et trouve les arguments
// mais il ft q les services soient définis ou aliasés/
//    ->setArguments( [
//        new Reference('database'),
//        new Reference('mailer.gmail'),
//        new Reference('texter.sms')
//    ])
    ->addMethodCall('sayHello', ["Message de réveil", 53])
    ->addMethodCall('setSecondaryMailer', [
        new Reference('mailer.gmail'),
    ]);
// 1- $controllerDefinition = new Definition(OrderController::class, [
// 1-     new Reference('database'),
// 1-     new Reference('mailer.gmail'),
// 1-     new Reference('texter.sms')
// 1- ]);
// 1- $controllerDefinition->addMethodCall('wakeup', ["Message de réveil"]);
// 1- $container->setDefinition('controller.order', $controllerDefinition);
//------------------------------------------------------------------------
$container->autowire('database', Database::class);
// 03-  $container->register('database', Database::class)
// 03-      ->setAutowired(true)
// 03-  );
// 2 -$container->register('database', Database::class);
// 1- $databaseDefinition = new Definition(Database::class);
// 1- $container->setDefinition('database', $databaseDefinition);
// 0- $database = new Database();
// --------------------------------------------------------------------
$container->autowire('texter.sms', SmsTexter::class)
// 03- $container->register('texter.sms', SmsTexter::class)
// 03-     ->setAutowired(true)
//$container->register('texter.sms', SmsTexter::class)
    ->setArguments(["service.sms.com", "apikey123"]);

$container->autowire('texter.fax', FaxTexter::class);
// 03- $container->register('texter.fax', FaxTexter::class)
// 03-     ->setAutowired(true)
//$container->register('texter.fax', FaxTexter::class)
// --------------------------------------------------------------------
$container->autowire('mailer.smtp', SmtpMailer::class)
// 03- $container->register('mailer.smtp', SmtpMailer::class)
// 03-     ->setAutowired(true)
// 2-$container->register('mailer.smtp', SmtpMailer::class)
   ->setArguments(["smtp://localhost", "root","123"]);

$container->autowire('mailer.gmail', GmailMailer::class)
// 03- $container->register('mailer.gmail', GmailMailer::class)
// 03-     ->setAutowired(true)
//2 - $container->register('mailer.gmail', GmailMailer::class)
    ->setArguments(["smtp://localhost", "root","123"]);

//-------------------------------------------------------------------------
$container->setAlias('App\controller\OrderController', 'order_controller')->setPublic(true);
$container->setAlias('App\Database\Database', 'database');
$container->setAlias('App\Mail\GMailMailer', 'mailer.gmail');
$container->setAlias('App\Mail\SmtpMailer', 'mailer.smtp');
$container->setAlias('App\Texter\SmsTexter', 'texter.sms');
$container->setAlias('App\Texter\FaxTexter', 'texter.fax');

$container->setAlias(MailerInterface::class, 'mailer.smtp');
$container->setAlias(TexterInterface::class, 'texter.fax');
$container->setAlias(Database::class, 'database');

$controllerDefinition = new Definition(OrderController::class, [
    new Reference(Database::class),
    new Reference(MailerInterface::class),
    new Reference(TexterInterface::class)
]);
$controllerDefinition->addMethodCall('wakeup', ["Message de réveil"]);

$container->setDefinition('controller.order', $controllerDefinition);
$container->setAlias(OrderController::class, 'controller.order')->setPublic(true);
$container->compile();
$controller = $container->get(OrderController::class);

$httpMethod = $_SERVER['REQUEST_METHOD'];

if ($httpMethod === 'POST') {
    $controller->placeOrder();
    return;
}

include __DIR__ . '/views/form.html.php';

