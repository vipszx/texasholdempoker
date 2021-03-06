
<?php

  // do something fancy for ppl who open same game in two browser windows

  require_once(dirname(__FILE__)."/../__config.php");

  $view = new view();
  $user = new user();
  $user->try_login();

  $game_id  = isset($_GET['id'])?$_GET['id']:0;
  $view->show_game      = false;
  $view->already_joined = false;

  if($game_id){
    $game   = game::load_by_id($game_id);

    if($game && $game->exists()){
      // whenever a user joins the game,
      // insert the user data to the game and reduce the game available spots
      if($game->phase !== "done"){
        if(!$game->already_joined($user)){
          if($game->has_spots_available() && $game->is_active()){
            $active_game_id = $user->get_active_game();
            $active_game    = game::load_by_id($active_game_id);
            if((!$active_game) || ($active_game_id == 0) || ($active_game_id == $game_id) || ($active_game->phase === "done")){
              if($game->join($user)){
                // load the view game
                $view->show_game  = true;
              }else{
                $view->error  = "Unexpected error encountered";
                metalog::log("Error", $view->error." user_id -> ".$user->id." ; game_id -> ".$game_id);
              }
            }else{
              $view->error  = "You can only have one game active at a time";
              metalog::log("Error", $view->error." user_id -> ".$user->id." ; game_id -> ".$game_id);
            }
          }else{
            $view->error  = "This game is not available to join anymore";
            metalog::log("Error", $view->error." user_id -> ".$user->id." ; game_id -> ".$game_id);
          }
        }else{
          // dont mark the player as joined
          $view->already_joined = true;
          $view->show_game      = true;
        }
      }else{
        $view->error  = "This game has already finished.";
        metalog::log("Error", $view->error." user_id -> ".$user->id." ; game_id -> ".$game_id);
      }
    }else{
      $view->error = "This game is invalid";
      metalog::log("Error", $view->error." user_id -> ".$user->id." ; game_id -> ".$game_id);
    }
  }

  $view->javascript[] = "js/game.js";
  $view->render("tpl.game.php");
