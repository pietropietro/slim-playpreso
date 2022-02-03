<?php

declare(strict_types=1);

use App\Repository\MatchRepository;
use App\Repository\PresoLeagueRepository;
use App\Repository\UserInPresoLeaguesRepository;
use App\Repository\GuessRepository;
use App\Repository\UserRepository;
use App\Repository\TrophyRepository;

use Psr\Container\ContainerInterface;

$container['user_repository'] = static fn (ContainerInterface $container): UserRepository => new UserRepository($container->get('db'));

$container['user_in_preso_leagues_repository'] = static fn (ContainerInterface $container): UserInPresoLeaguesRepository => new UserInPresoLeaguesRepository($container->get('db'));

$container['preso_league_repository'] = static fn (ContainerInterface $container): PresoLeagueRepository => new PresoLeagueRepository($container->get('db'));

$container['guess_repository'] = static fn (ContainerInterface $container): GuessRepository => new GuessRepository($container->get('db'));

$container['match_repository'] = static fn (ContainerInterface $container): MatchRepository => new MatchRepository($container->get('db'));

$container['trophy_repository'] = static fn (ContainerInterface $container): TrophyRepository => new TrophyRepository($container->get('db'));
