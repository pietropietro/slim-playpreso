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
use App\Service\PPArea;
use App\Service\UserParticipation;
use App\Service\League;
use App\Service\ExternalAPI;
use App\Service\Match;
use App\Service\Guess;
use App\Service\Points;
use App\Service\Team;
use App\Service\EmailPreferences;
use App\Service\EmailBuilder;
use App\Service\Stats;
use App\Service\Trophy;
use App\Service\MOTD;
use App\Service\HttpClientService;
use App\Service\PushNotifications;
use App\Service\UserNotification;
use App\Service\PPDex;
use App\Service\PPRanking;
use App\Service\Highlights;
use App\Service\Flash;
use Psr\Container\ContainerInterface;


//TODO REFACTOR IN DIFFERENT FILES

$container['user_find_service'] = static fn (
    ContainerInterface $container
): User\Find => new User\Find(
    $container->get('user_repository'),
    $container->get('redis_service'),
    $container->get('userparticipation_find_service'),
    $container->get('guess_find_service'),
    $container->get('trophy_find_service'),
    $container->get('ppranking_find_service'),
    $container->get('motd_leader_service'),
    $container->get('flash_find_service')  
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

$container['pptournamenttype_find_service'] = static fn (
    ContainerInterface $container
):  PPTournamentType\Find => new  PPTournamentType\Find(
    $container->get('redis_service'),
    $container->get('pptournamenttype_repository'),
    $container->get('userparticipation_repository'),
    $container->get('pproundmatch_repository'),
    $container->get('points_find_service'),
    $container->get('league_find_service'),
    $container->get('trophy_find_service'),
);

$container['ppleague_update_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Update => new  PPLeague\Update(
    $container->get('ppleague_repository'),
    $container->get('userparticipation_find_service'),
    $container->get('userparticipation_update_service'),
    $container->get('pptournamenttype_find_service'),
    $container->get('pptournamenttype_join_service'),
    $container->get('points_update_service'),
    $container->get('usernotification_create_service')
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
);

$container['pptournament_verifyafterjoin_service'] = static fn (
    ContainerInterface $container
):  PPTournament\VerifyAfterJoin => new  PPTournament\VerifyAfterJoin(
    $container->get('ppleague_repository'),
    $container->get('userparticipation_find_service'),
    $container->get('userparticipation_update_service'),
    $container->get('pptournamenttype_find_service'),
    $container->get('ppcupgroup_find_service'),
    $container->get('ppround_create_service'),
    $container->get('ppcup_update_service')
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

$container['pptournamenttype_create_service'] = static fn (
    ContainerInterface $container
):  PPTournamentType\Create => new  PPTournamentType\Create(
    $container->get('pptournamenttype_repository'),
);

$container['ppround_find_service'] = static fn (
    ContainerInterface $container
):  PPRound\Find => new  PPRound\Find(
    $container->get('redis_service'),
    $container->get('pproundmatch_find_service'),
    $container->get('match_find_service'),
    $container->get('ppround_repository'),
    $container->get('pptournamenttype_repository'),    
);

$container['pproundmatch_find_service'] = static fn (
    ContainerInterface $container
):  PPRoundMatch\Find => new  PPRoundMatch\Find(
    $container->get('redis_service'),
    $container->get('pproundmatch_repository'),
    $container->get('guess_repository'),
    $container->get('match_find_service')
);

$container['motd_find_service'] = static fn (
    ContainerInterface $container
):  MOTD\Find => new  MOTD\Find(
    $container->get('redis_service'),
    $container->get('motd_repository'),
    $container->get('pproundmatch_find_service'),
    $container->get('match_find_service'),
    $container->get('guess_find_service')
);

$container['motd_create_service'] = static fn (
    ContainerInterface $container
):  MOTD\Create => new  MOTD\Create(
    $container->get('motd_repository'),
);

$container['motd_leader_service'] = static fn (
    ContainerInterface $container
):  MOTD\Leader => new  MOTD\Leader(
    $container->get('redis_service'),
    $container->get('motd_repository'),
    $container->get('motd_find_service'),
    $container->get('guess_repository'),
    $container->get('points_update_service')
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
    $container->get('pptournamenttype_find_service'),
    $container->get('ppleague_repository'),
    $container->get('ppcupgroup_repository'),
    $container->get('ppround_find_service'),
    $container->get('match_find_service'),
    $container->get('trophy_find_service')
);

$container['userparticipation_create_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Create => new  UserParticipation\Create(
    $container->get('userparticipation_repository'),
    $container->get('ppcupgroup_repository'),
    $container->get('pptournament_verifyafterjoin_service'),
);

$container['userparticipation_update_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Update => new  UserParticipation\Update(
    $container->get('userparticipation_repository'),
    $container->get('guess_repository'),
);

$container['points_update_service'] = static fn (
    ContainerInterface $container
):  Points\Update => new  Points\Update(
    $container->get('user_repository'),
    $container->get('guess_repository')
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
    $container->get('match_repository'),
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
    $container->get('usernotification_create_service'),
);

$container['ppcupgroup_find_service'] = static fn (
    ContainerInterface $container
):  PPCupGroup\Find => new  PPCupGroup\Find(
    $container->get('redis_service'),
    $container->get('ppcupgroup_repository'),
    $container->get('userparticipation_find_service'),
    $container->get('ppround_find_service'),
    $container->get('pptournamenttype_repository')
);

$container['external_api_importleaguedata_service'] = static fn (
    ContainerInterface $container
):  ExternalAPI\ImportLeagueData => new  ExternalAPI\ImportLeagueData(
    $container->get('match_elaborate_service'),
    $container->get('league_elaborate_service'),
    $container->get('league_update_service'),
    $container->get('team_create_service'),
    $container->get('httpclient_service'),
);

$container['external_api_importteamlogo_service'] = static fn (
    ContainerInterface $container
):  ExternalAPI\ImportTeamLogo => new  ExternalAPI\ImportTeamLogo(
    $container->get('httpclient_service')
);

$container['match_elaborate_service'] = static fn (
    ContainerInterface $container
):  Match\Elaborate => new  Match\Elaborate(
    $container->get('match_create_service'),
    $container->get('match_verify_service'),
    $container->get('match_update_service'),
    $container->get('match_find_service'),
    $container->get('team_find_service'),
    $container->get('team_create_service'),
    $container->get('ppround_update_service'),
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

$container['team_create_service'] = static fn (
    ContainerInterface $container
):  Team\Create => new  Team\Create(
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
    $container->get('motd_leader_service'),
    $container->get('flash_verify_service')
);

$container['guess_verify_service'] = static fn (
    ContainerInterface $container
):  Guess\Verify => new  Guess\Verify(
    $container->get('redis_service'),
    $container->get('guess_repository'),
    $container->get('points_calculate_service'),
    $container->get('points_update_service'),
    $container->get('usernotification_create_service')
);

$container['guess_find_service'] = static fn (
    ContainerInterface $container
):  Guess\Find => new  Guess\Find(
    $container->get('redis_service'),
    $container->get('guess_repository'),
    $container->get('match_find_service'),
    $container->get('pptournamenttype_find_service')
);


$container['points_calculate_service'] = static fn (
    ContainerInterface $container
):  Points\Calculate => new  Points\Calculate();

$container['match_picker_service'] = static fn (
    ContainerInterface $container
):  Match\Picker => new Match\Picker(
    $container->get('match_repository'),
    $container->get('league_find_service'),
    $container->get('pptournamenttype_repository'),
);

$container['match_find_service'] = static fn (
    ContainerInterface $container
):  Match\Find => new Match\Find(
    $container->get('match_repository'),
    $container->get('league_find_service'),
    $container->get('team_find_service'),
);

$container['match_extract_summary_service'] = static fn (
    ContainerInterface $container
):  Match\ExtractSummary => new Match\ExtractSummary(
    $container->get('match_repository'),
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
);

$container['pproundmatch_delete_service'] = static fn (
    ContainerInterface $container
):  PPRoundMatch\Delete => new  PPRoundMatch\Delete(
    $container->get('pproundmatch_repository'),
    $container->get('guess_repository'),
);


$container['guess_create_service'] = static fn (
    ContainerInterface $container
):  Guess\Create => new  Guess\Create(
    $container->get('guess_repository'),
    $container->get('userparticipation_repository'),
    $container->get('pproundmatch_repository'),
    $container->get('match_repository'),
    $container->get('user_repository')
);

$container['guess_lock_service'] = static fn (
    ContainerInterface $container
):  Guess\Lock => new  Guess\Lock(
    $container->get('guess_repository'),
    $container->get('match_repository'),
);

$container['pptournament_verifyafterround_service'] = static fn (
    ContainerInterface $container
):  PPTournament\VerifyAfterRound => new  PPTournament\VerifyAfterRound(
    $container->get('pptournamenttype_find_service'),
    $container->get('ppcupgroup_find_service'),
    $container->get('ppround_find_service'),
    $container->get('ppround_create_service'),
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

$container['stats_find_service'] = static fn (
    ContainerInterface $container
):  Stats\Find => new  Stats\Find(
    $container->get('stats_repository'),
    $container->get('user_find_service'),
    $container->get('pptournamenttype_find_service'),
);

$container['stats_user_service'] = static fn (
    ContainerInterface $container
):  Stats\User => new  Stats\User(
    $container->get('stats_repository'),
    $container->get('redis_service'),
    $container->get('guess_find_service'),
    $container->get('ppround_find_service')
);

$container['stats_find_adjacent_ups_service'] = static fn (
    ContainerInterface $container
):  Stats\FindAdjacentUps => new  Stats\FindAdjacentUps(
    $container->get('userparticipation_repository'),
    $container->get('pptournamenttype_find_service'),
);

$container['stats_calculate_year_wrapped_service'] = static fn (
    ContainerInterface $container
):  Stats\CalculateYearWrapped => new  Stats\CalculateYearWrapped(
    $container->get('stats_repository'),
    $container->get('userparticipation_repository'),
    $container->get('pptournamenttype_repository'),
    $container->get('trophy_find_service'),
    $container->get('stats_find_adjacent_ups_service')
);

$container['trophy_find_service'] = static fn (
    ContainerInterface $container
):  Trophy\Find => new  Trophy\Find(
    $container->get('redis_service'),
    $container->get('userparticipation_repository'),
    $container->get('pptournamenttype_repository')
);

$container['pparea_find_service'] = static fn (
    ContainerInterface $container
):  PPArea\Find => new  PPArea\Find(
    $container->get('redis_service'),
    $container->get('league_find_service'),
    $container->get('pparea_repository')
);

$container['pparea_create_service'] = static fn (
    ContainerInterface $container
):  PPArea\Create => new  PPArea\Create(
    $container->get('pparea_repository')
);


$container['pparea_update_service'] = static fn (
    ContainerInterface $container
):  PPArea\Update => new  PPArea\Update(
    $container->get('pparea_repository')
);

$container['httpclient_service'] = static fn (
    ContainerInterface $container
): HttpClientService => new HttpClientService(
    $container->get('guzzle_client')
);


$container['pushnotifications_send_service'] = static fn (
    ContainerInterface $container
):  PushNotifications\Send => new  PushNotifications\Send(
    $container->get('devicetoken_repository'),
    $container->get('apns_client'),
    $container->get('firebase_messaging')
);

$container['usernotification_create_service'] = static fn (
    ContainerInterface $container
):  UserNotification\Create => new  UserNotification\Create(
    $container->get('usernotification_repository'),
    $container->get('pushnotificationpreferences_repository'),
    $container->get('guess_find_service'),
    $container->get('pushnotifications_send_service'),
);

$container['usernotification_find_service'] = static fn (
    ContainerInterface $container
):  UserNotification\Find => new  UserNotification\Find(
    $container->get('usernotification_repository'),
    $container->get('guess_find_service'),
    $container->get('pproundmatch_repository'),
    $container->get('userparticipation_find_service')
);

$container['usernotification_read_service'] = static fn (
    ContainerInterface $container
):  UserNotification\Read => new  UserNotification\Read(
    $container->get('usernotification_repository'),
);

$container['ppdex_find_service'] = static fn (
    ContainerInterface $container
):  PPDex\Find => new  PPDex\Find(
    $container->get('redis_service'),
    $container->get('ppdex_repository'),
    $container->get('pptournamenttype_find_service')
);

$container['ppranking_calculate_service'] = static fn (
    ContainerInterface $container
):  PPRanking\Calculate => new  PPRanking\Calculate(
    $container->get('ppranking_repository'),
);

$container['ppranking_find_service'] = static fn (
    ContainerInterface $container
):  PPRanking\Find => new  PPRanking\Find(
    $container->get('ppranking_repository'),
    $container->get('pptournamenttype_repository'),
    $container->get('userparticipation_find_service'),
    $container->get('ppranking_calculate_service'),
);


$container['ppround_update_service'] = static fn (
    ContainerInterface $container
):  PPRound\Update => new  PPRound\Update(
    $container->get('pproundmatch_update_service'),
    $container->get('match_picker_service'),
    $container->get('ppround_repository'),
);


$container['highlights_lastpresos_service'] = static fn (
    ContainerInterface $container
):  Highlights\LastPresos => new  Highlights\LastPresos(
    $container->get('redis_service'),
    $container->get('highlights_repository'),
    $container->get('guess_repository'),
    $container->get('pptournamenttype_find_service'),
    $container->get('match_find_service'),
    $container->get('user_find_service')
);


$container['flash_create_service'] = static fn (
    ContainerInterface $container
):  Flash\Create => new  Flash\Create(
    $container->get('flash_repository'),
    $container->get('match_repository')
);

$container['flash_find_service'] = static fn (
    ContainerInterface $container
):  Flash\Find => new  Flash\Find(
    $container->get('flash_repository'),
    $container->get('pproundmatch_find_service')
);

$container['flash_verify_service'] = static fn (
    ContainerInterface $container
):  Flash\Verify => new  Flash\Verify(
    $container->get('flash_repository'),
    $container->get('guess_repository'),
    $container->get('points_update_service')
);



