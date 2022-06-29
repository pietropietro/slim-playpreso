<?php

declare(strict_types=1);

use App\Repository\MatchRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\PPRoundRepository;
use App\Repository\PPRoundMatchRepository;
use App\Repository\PPCupRepository;
use App\Repository\PPCupGroupRepository;
use App\Repository\GuessRepository;
use App\Repository\UserRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\LeagueRepository;

use Psr\Container\ContainerInterface;

$container['user_repository'] = static fn (ContainerInterface $container): UserRepository => new UserRepository($container->get('db'));

$container['user_participations_repository'] = static fn (ContainerInterface $container): UserParticipationRepository => new UserParticipationRepository($container->get('db'));

$container['league_repository'] = static fn (ContainerInterface $container): LeagueRepository => new LeagueRepository($container->get('db'));

$container['ppleague_repository'] = static fn (ContainerInterface $container): PPLeagueRepository => new PPLeagueRepository($container->get('db'));

$container['ppleaguetype_repository'] = static fn (ContainerInterface $container): PPLeagueTypeRepository => new PPLeagueTypeRepository($container->get('db'));

$container['guess_repository'] = static fn (ContainerInterface $container): GuessRepository => new GuessRepository($container->get('db'));

$container['match_repository'] = static fn (ContainerInterface $container): MatchRepository => new MatchRepository($container->get('db'));

$container['ppround_repository'] = static fn (ContainerInterface $container): PPRoundRepository => new PPRoundRepository($container->get('db'));

$container['ppround_match_repository'] = static fn (ContainerInterface $container): PPRoundMatchRepository => new PPRoundMatchRepository($container->get('db'));

$container['ppcup_repository'] = static fn (ContainerInterface $container): PPCupRepository => new PPCupRepository($container->get('db'));

$container['ppcupgroup_repository'] = static fn (ContainerInterface $container): PPCupGroupRepository => new PPCupGroupRepository($container->get('db'));
