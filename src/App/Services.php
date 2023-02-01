<?php

declare(strict_types=1);

use App\Service\User;
use App\Service\PPLeague;
use App\Service\PPTournament;
use App\Service\PPTournamentType;
use App\Service\PPRound;
use App\Service\PPRoundMatch;
use App\Service\PPCup;
use App\Service\PPCupGroup;
use App\Service\UserParticipation;
use App\Service\League;
use App\Service\ExternalAPI;
use App\Service\Match;
use App\Service\Guess;
use App\Service\Points;
use App\Service\Team;
use App\Service\EmailPreferences;
use App\Service\EmailBuilder;
use Psr\Container\ContainerInterface;

$container['user_find_service'] = static fn (
    ContainerInterface $container
): User\Find => new User\Find(
    $container->get('user_repository'),
    $container->get('redis_service'),
    $container->get('userparticipation_find_service'),
    $container->get('guess_find_service'),
);

$container['user_create_service'] = static fn (
    ContainerInterface $container
): User\Create => new User\Create(
    $container->get('user_repository'),
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
    $container->get('emailpreferences_repository'),
    $container->get('redis_service')
);

//TODO REMOVE UNUSED SERVICES REPO
$container['ppleague_find_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Find => new  PPLeague\Find(
    $container->get('redis_service'),
    $container->get('ppleague_repository'),
    $container->get('pptournamenttype_find_service'),
);

$container['ppleague_update_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Update => new  PPLeague\Update(
    $container->get('ppleague_repository'),
);

$container['pptournamenttype_find_service'] = static fn (
    ContainerInterface $container
):  PPTournamentType\Find => new  PPTournamentType\Find(
    $container->get('redis_service'),
    $container->get('pptournamenttype_repository'),
    $container->get('userparticipation_repository'),
    $container->get('points_find_service'),
    $container->get('league_find_service'),
);

$container['pptournamenttype_join_service'] = static fn (
    ContainerInterface $container
):  PPTournamentType\Join => new  PPTournamentType\Join(
    $container->get('ppleague_find_service'),
    $container->get('pptournamenttype_find_service'),
    $container->get('points_update_service'),
    $container->get('userparticipation_create_service'),
    $container->get('ppcup_find_service'),
    $container->get('ppcupgroup_find_service'),
    $container->get('pptournament_verifyafterjoin_service'),
);

$container['pptournamenttype_check_service'] = static fn (
    ContainerInterface $container
):  PPTournamentType\Check => new  PPTournamentType\Check(
    $container->get('pptournamenttype_find_service'),
    $container->get('points_find_service'),
    $container->get('ppcup_repository'),
    $container->get('userparticipation_find_service')
);

$container['pptournamenttype_update_service'] = static fn (
    ContainerInterface $container
):  PPTournamentType\Update => new  PPTournamentType\Update(
    $container->get('pptournamenttype_repository'),
);

$container['ppround_find_service'] = static fn (
    ContainerInterface $container
):  PPRound\Find => new  PPRound\Find(
    $container->get('redis_service'),
    $container->get('pproundmatch_find_service'),
    $container->get('match_find_service'),
    $container->get('ppround_repository'),
    $container->get('guess_repository'),
);

$container['pproundmatch_find_service'] = static fn (
    ContainerInterface $container
):  PPRoundMatch\Find => new  PPRoundMatch\Find(
    $container->get('redis_service'),
    $container->get('pproundmatch_repository'),
    $container->get('guess_repository'),
    $container->get('match_find_service')
);


$container['ppround_verify_service'] = static fn (
    ContainerInterface $container
):  PPRound\Verify => new  PPRound\Verify(
    $container->get('ppround_find_service'),
    $container->get('pptournament_verifyafterround_service'),
    $container->get('userparticipation_update_service'),
);


//TODO understand how to remove duplicates 
//different constructros in base services vs detailed?
$container['userparticipation_find_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Find => new  UserParticipation\Find(
    $container->get('redis_service'),
    $container->get('userparticipation_repository'),
    $container->get('pptournamenttype_repository'),
    $container->get('ppleague_repository'),
    $container->get('guess_repository'),
    $container->get('ppround_find_service'),
    $container->get('match_find_service'),
);

$container['userparticipation_create_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Create => new  UserParticipation\Create(
    $container->get('userparticipation_repository')
);

$container['userparticipation_update_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Update => new  UserParticipation\Update(
    $container->get('redis_service'),
    $container->get('userparticipation_repository'),
    $container->get('pptournamenttype_repository'),
    $container->get('ppleague_repository'),
    $container->get('guess_repository'),
    $container->get('ppround_find_service'),
    $container->get('match_find_service'),
);

$container['points_update_service'] = static fn (
    ContainerInterface $container
):  Points\Update => new  Points\Update(
    $container->get('user_repository')
);

$container['points_find_service'] = static fn (
    ContainerInterface $container
):  Points\Find => new  Points\Find(
    $container->get('user_repository')
);

$container['league_find_service'] = static fn (
    ContainerInterface $container
):  League\Find => new  League\Find(
    $container->get('redis_service'),
    $container->get('pptournamenttype_repository'),
    $container->get('league_repository'),
);

$container['league_update_service'] = static fn (
    ContainerInterface $container
):  League\Update => new  League\Update(
    $container->get('league_repository'),
);

$container['league_elaborate_service'] = static fn (
    ContainerInterface $container
):  League\Elaborate => new  League\Elaborate(
    $container->get('league_repository'),
    $container->get('team_repository'),
);

$container['league_create_service'] = static fn (
    ContainerInterface $container
):  League\Create => new  League\Create(
    $container->get('league_repository')
);

$container['ppcup_count_service'] = static fn (
    ContainerInterface $container
):  PPCup\Count => new  PPCup\Count(
    $container->get('redis_service'),
    $container->get('userparticipation_update_service'),
    $container->get('ppcupgroup_repository'),
);

$container['ppcup_find_service'] = static fn (
    ContainerInterface $container
):  PPCup\Find => new  PPCup\Find(
    $container->get('redis_service'),
    $container->get('ppcup_repository'),
    $container->get('ppcupgroup_find_service'),
    $container->get('pptournamenttype_find_service'),
    $container->get('userparticipation_find_service'),
);

$container['ppcup_create_service'] = static fn (
    ContainerInterface $container
):  PPCup\Create => new  PPCup\Create(
    $container->get('ppcup_repository'),
    $container->get('ppcupgroup_repository'),
    $container->get('pptournamenttype_check_service'),
    $container->get('pptournamenttype_find_service'),
);

$container['ppcup_update_service'] = static fn (
    ContainerInterface $container
):  PPCup\Update => new  PPCup\Update(
    $container->get('ppcup_repository'),
    $container->get('ppcupgroup_repository'),
    $container->get('ppcupgroup_find_service'),
    $container->get('pptournamenttype_find_service'),
    $container->get('ppround_create_service'),
);

$container['ppcupgroup_update_service'] = static fn (
    ContainerInterface $container
):  PPCupGroup\Update => new  PPCupGroup\Update(
    $container->get('ppcupgroup_repository'),
    $container->get('ppcup_repository'),
    $container->get('ppcupgroup_find_service'),
    $container->get('userparticipation_find_service'),
    $container->get('userparticipation_create_service'),
    $container->get('pptournamenttype_find_service'),
    $container->get('pptournament_verifyafterjoin_service'),
);

$container['ppcupgroup_find_service'] = static fn (
    ContainerInterface $container
):  PPCupGroup\Find => new  PPCupGroup\Find(
    $container->get('redis_service'),
    $container->get('ppcupgroup_repository'),
    $container->get('userparticipation_find_service'),
    $container->get('ppround_find_service')
);

$container['external_api_service'] = static fn (
    ContainerInterface $container
):  ExternalAPI\Call => new  ExternalAPI\Call(
    $container->get('match_elaborate_service'),
    $container->get('league_elaborate_service'),
    $container->get('team_elaborate_service'),
    
);

$container['match_elaborate_service'] = static fn (
    ContainerInterface $container
):  Match\Elaborate => new  Match\Elaborate(
    $container->get('match_create_service'),
    $container->get('match_verify_service'),
    $container->get('match_update_service'),
    $container->get('match_find_service'),
    $container->get('team_find_service'),
);

$container['match_update_service'] = static fn (
    ContainerInterface $container
):  Match\Update => new  Match\Update(
    $container->get('match_repository'),
    $container->get('team_repository'),
);

$container['match_delete_service'] = static fn (
    ContainerInterface $container
):  Match\Delete => new  Match\Delete(
    $container->get('match_repository'),
);

$container['team_find_service'] = static fn (
    ContainerInterface $container
):  Team\Find => new  Team\Find(
    $container->get('team_repository'),
);

$container['team_elaborate_service'] = static fn (
    ContainerInterface $container
):  Team\Elaborate => new  Team\Elaborate(
    $container->get('team_repository'),
);

$container['match_create_service'] = static fn (
    ContainerInterface $container
):  Match\Create => new  Match\Create(
    $container->get('match_repository'),
    $container->get('team_repository'),
);

$container['match_verify_service'] = static fn (
    ContainerInterface $container
):  Match\Verify => new  Match\Verify(
    $container->get('match_repository'),
    $container->get('guess_verify_service'),
    $container->get('ppround_verify_service'),
);

$container['guess_verify_service'] = static fn (
    ContainerInterface $container
):  Guess\Verify => new  Guess\Verify(
    $container->get('guess_repository'),
    $container->get('points_calculate_service'),
    $container->get('points_update_service')
);

$container['guess_find_service'] = static fn (
    ContainerInterface $container
):  Guess\Find => new  Guess\Find(
    $container->get('guess_repository'),
    $container->get('match_find_service'),
    $container->get('ppround_find_service'),
    $container->get('pproundmatch_find_service')
);


$container['points_calculate_service'] = static fn (
    ContainerInterface $container
):  Points\Calculate => new  Points\Calculate();

$container['match_picker_service'] = static fn (
    ContainerInterface $container
):  Match\Picker => new Match\Picker(
    $container->get('match_repository'),
    $container->get('league_find_service'),
);

$container['match_find_service'] = static fn (
    ContainerInterface $container
):  Match\Find => new Match\Find(
    $container->get('match_repository'),
    $container->get('league_find_service'),
    $container->get('team_find_service'),
);

$container['ppround_create_service'] = static fn (
    ContainerInterface $container
):  PPRound\Create => new  PPRound\Create(
    $container->get('match_picker_service'),
    $container->get('ppround_repository'),
    $container->get('pproundmatch_create_service'),
);

$container['pproundmatch_create_service'] = static fn (
    ContainerInterface $container
):  PPRoundMatch\Create => new  PPRoundMatch\Create(
    $container->get('pproundmatch_repository'),
    $container->get('guess_create_service'),
);

$container['pproundmatch_update_service'] = static fn (
    ContainerInterface $container
):  PPRoundMatch\Update => new  PPRoundMatch\Update(
    $container->get('pproundmatch_repository'),
    $container->get('guess_repository'),
);


$container['guess_create_service'] = static fn (
    ContainerInterface $container
):  Guess\Create => new  Guess\Create(
    $container->get('guess_repository'),
    $container->get('userparticipation_repository'),
);

$container['guess_lock_service'] = static fn (
    ContainerInterface $container
):  Guess\Lock => new  Guess\Lock(
    $container->get('guess_repository'),
    $container->get('match_repository'),
);


$container['pptournament_verifyafterjoin_service'] = static fn (
    ContainerInterface $container
):  PPTournament\VerifyAfterJoin => new  PPTournament\VerifyAfterJoin(
    $container->get('userparticipation_find_service'),
    $container->get('userparticipation_update_service'),
    $container->get('pptournamenttype_find_service'),
    $container->get('ppcupgroup_find_service'),
    $container->get('ppleague_update_service'),
    $container->get('ppround_create_service'),
    $container->get('ppcup_update_service')
);

$container['pptournament_verifyafterround_service'] = static fn (
    ContainerInterface $container
):  PPTournament\VerifyAfterRound => new  PPTournament\VerifyAfterRound(
    $container->get('pptournamenttype_find_service'),
    $container->get('ppcupgroup_find_service'),
    $container->get('ppround_find_service'),
    $container->get('ppround_create_service'),
    $container->get('userparticipation_update_service'),
    $container->get('ppleague_update_service'),
    $container->get('ppcupgroup_update_service'),
);

$container['user_recover_service'] = static fn (
    ContainerInterface $container
):  User\Recover => new  User\Recover(
    $container->get('userrecover_repository'),
);

$container['emailpreferences_update_service'] = static fn (
    ContainerInterface $container
):  EmailPreferences\Update => new  EmailPreferences\Update(
    $container->get('emailpreferences_repository'),
);

$container['emailpreferences_find_service'] = static fn (
    ContainerInterface $container
):  EmailPreferences\Find => new  EmailPreferences\Find(
    $container->get('emailpreferences_repository'),
);

$container['emailbuilder_lockreminder_service'] = static fn (
    ContainerInterface $container
):  EmailBuilder\LockReminder => new  EmailBuilder\LockReminder(
    $container->get('match_find_service'),
);