<?php

$app->post('/auth', "\App\Action\User:login");
$app->post('/user', "\App\Action\User:signup");