<?php

declare(strict_types=1);

use App\Repository\MatchRepository;
use App\Repository\GuessRepository;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;

$container['user_repository'] = static fn (ContainerInterface $container): UserRepository => new UserRepository($container->get('db'));

$container['task_repository'] = static fn (ContainerInterface $container): GuessRepository => new GuessRepository($container->get('db'));

$container['note_repository'] = static fn (ContainerInterface $container): MatchRepository => new MatchRepository($container->get('db'));
