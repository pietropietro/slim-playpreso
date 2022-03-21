<?php

declare(strict_types=1);

use App\Service\User;
use Psr\Container\ContainerInterface;

$container['find_user_service'] = static fn (
    ContainerInterface $container
): User\Find => new User\Find(
    $container->get('user_repository'),
    $container->get('pp_league_repository'),
    $container->get('user_participations_repository'),
    $container->get('guess_repository'),
    $container->get('match_repository'),
    $container->get('pp_league_type_repository'),
    $container->get('redis_service'),
);

$container['create_user_service'] = static fn (
    ContainerInterface $container
): User\Create => new User\Create(
    $container->get('user_repository'),
    $container->get('pp_league_repository'),
    $container->get('user_participations_repository'),
    $container->get('guess_repository'),
    $container->get('match_repository'),
    $container->get('redis_service')
);

$container['update_user_service'] = static fn (
    ContainerInterface $container
): User\Update => new User\Update(
    $container->get('user_repository'),
    $container->get('redis_service')
);

$container['delete_user_service'] = static fn (
    ContainerInterface $container
): User\Delete => new User\Delete(
    $container->get('user_repository'),
    $container->get('redis_service')
);

$container['login_user_service'] = static fn (
    ContainerInterface $container
): User\Login => new User\Login(
    $container->get('user_repository'),
    $container->get('pp_league_repository'),
    $container->get('user_participations_repository'),
    $container->get('guess_repository'),
    $container->get('match_repository'),
    $container->get('redis_service')
);

$container['guess_service'] = static fn (
    ContainerInterface $container
): GuessService => new GuessService(
    $container->get('guess_repository'),
    $container->get('redis_service')
);



