<?php 

namespace Battlegrounds; 

use pocketmine\plugin\PluginBase; 
use pocketmine\command\Command; 
use pocketmine\command\CommandSender; 
use pocketmine\utils\Config; 
use pocketmine\utils\TextFormat; 
use pocketmine\Player; 
use pocketmine\event\Listener; 
use pocketmine\event\player\PlayerInteractEvent; 
use pocketmine\item\Item; 
use pocketmine\event\block\BlockBreakEvent; 
use pocketmine\event\block\BlockPlaceEvent; 
use pocketmine\level\Position; 
use pocketmine\event\player\PlayerJoinEvent; 
use pocketmine\event\player\PlayerChatEvent; 
use pocketmine\event\player\PlayerQuitEvent; 
use pocketmine\event\player\PlayerKickEvent; 
use pocketmine\plugin\Plugin; 

use Battlegrounds\arenas\Arena; 

class Battlegrounds extends PluginBase implements Listener { 
	
              public $cfg; 
              public $msg; 
              public $arenas = []; 
              public $ins = []; 
              public $selectors = []; 
              public $inv = []; 
              public $setters = []; 
              public $economy; 

             public function onEnable(): void{ 
                $this->initConfig(); 
                $this->registerEconomy(); 
                $this->checkArenas(); 
                $this->getServer()->getPluginManager()->registerEvents($this, $this); 
                
                if(!$this->getServer()->isLevelGenerated($this->cfg->getNested('lobby.world'))){ 
                $this->getServer()->generateLevel($this->cfg->getNested('lobby.world')); 
                      } 
                $this->getLogger()->info(TextFormat::GREEN."A shooting action minigame, Battlegrounds is online!"); 
                $this->checkAgain(); 
              } 

              public function onDisable(): void{ 
                 $this->getLogger()->info(TextFormat::RED."A shooting action minigame, Battlegrounds is offline!"); 
              }
              
              public function setArenasData(Config $arena, $name) { 
                 $this->arenas[$name] = $arena->getAll(); 
                 $this->arenas[$name]['enable'] = true; 
                 $game = new Arena($name, $this); 
                 $game->enableScheduler(); 
                 $this->ins[$name] = $game; 
                 $this->getServer()->getPluginManager()->registerEvents($game, $this); 
               }
               
               public function initConfig() { 
                  if(!file_exists($this->getDataFolder())){ 
                  @mkdir($this->getDataFolder()); 
                          } 
                  if(!is_file($this->getDataFolder()."config.yml")){ 
                  $this->saveResource("config.yml"); 
                        } 
                  $this->cfg = new Config($this->getDataFolder()."config.yml", Config::YAML); 
                  if(!file_exists($this->getDataFolder()."arenas/")){ 
                  @mkdir($this->getDataFolder()."arenas/"); 
                  $this->saveResource("arenas/default.yml"); 
                       } 
                  if(!file_exists($this->getDataFolder()."languages/")){ 
                  @mkdir($this->getDataFolder()."languages/"); 
                      } 
                  if(!is_file($this->getDataFolder()."languages/English.yml")){ 
                  $this->saveResource("languages/English.yml"); 
                    } 
                  if(!is_file($this->getDataFolder()."languages/{$this->cfg->get('Language')}.yml")){ 
                  $this->msg = new Config($this->getDataFolder()."languages/English.yml", Config::YAML); 
                  $this->getServer()->getLogger()->info("Selected plugin language to English"); 
                    } else { 
                  $this->msg = new Config($this->getDataFolder()."languages/{$this->cfg->get('Language')}.yml", Config::YAML); 
                  $this->getServer()->getLogger()->info("Selected plugin language {$this->cfg->get('Language')}"); 
                 } 
             }
             
                     public function checkArenas() { 
                     $this->getLogger()->info("checking arena files..."); 
                        foreach(glob($this->getDataFolder()."arenas/*.yml") as $file){ 
                         $arena = new Config($file, Config::YAML); 
                         
                          if(strtolower($arena->get("enabled")) === "false"){ 
                          $this->arenas[basename($file, ".yml")] = $arena->getAll(); 
                          $this->arenas[basename($file, ".yml")]['enable'] = false; 
                             } else { 
                            if($this->checkFile($arena) === true){ 
                            $fname = basename($file); 
                            $this->setArenasData($arena, basename($file, ".yml")); 
                             $this->getLogger()->info("$fname - ".TextFormat::GREEN."Checking data has sucessful!"); 
                                 } else { 
                             $this->arenas[basename($file, ".yml")] = $arena->getAll(); 
                             $this->arenas[basename($file, ".yml")]['enable'] = false; 
                              $fname = basename($file, ".yml"); $this->getLogger()->error("Arena \"$fname\" is not set properly"); 
                               } 
                           } 
                         } 
                     }
                           
                                        public function checkFile(Config $arena) { 
                                        if(!(is_numeric($arena->getNested("signs.join_sign_x")) && is_numeric($arena->getNested("signs.join_sign_y")) && is_numeric($arena->getNested("signs.join_sign_z")) && is_string($arena->getNested("signs.join_sign_world")) && is_string($arena->getNested("signs.status_line_1")) && is_string($arena->getNested("signs.status_line_2")) && is_string($arena->getNested("signs.status_line_3")) && is_string($arena->getNested("signs.status_line_4")) && is_numeric($arena->getNested("signs.return_sign_x")) && is_numeric($arena->getNested("signs.return_sign_y")) && is_numeric($arena->getNested("signs.return_sign_z")) && is_string($arena->getNested("arena.arena_world")) && is_numeric($arena->getNested("arena.join_position_x")) && is_numeric($arena->getNested("arena.join_position_y")) && is_numeric($arena->getNested("arena.join_position_z")) && is_numeric($arena->getNested("arena.lobby_position_x")) && is_numeric($arena->getNested("arena.lobby_position_y")) && is_numeric($arena->getNested("arena.lobby_position_z")) && is_string($arena->getNested("arena.lobby_position_world")) && is_numeric($arena->getNested("arena.spec_spawn_x")) && is_numeric($arena->getNested("arena.spec_spawn_y")) && is_numeric($arena->getNested("arena.spec_spawn_z")) && is_numeric($arena->getNested("arena.leave_position_x")) && is_numeric($arena->getNested("arena.leave_position_y")) && is_numeric($arena->getNested("arena.leave_position_z")) && is_string($arena->getNested("arena.leave_position_world")) && is_numeric($arena->getNested("arena.max_game_time")) && is_numeric($arena->getNested("arena.max_players")) && is_numeric($arena->getNested("arena.min_players")) && is_numeric($arena->getNested("arena.starting_time")) && is_numeric($arena->getNested("arena.floor_y")) && is_string($arena->getNested("arena.finish_msg_levels")) && !is_string($arena->getNested("arena.money_reward")))){ 
                                               return false; 
                                                   } 
                                                    if(!((strtolower($arena->getNested("signs.enable_status")) == "true" || strtolower($arena->getNested("signs.enable_status")) == "false") && (strtolower($arena->getNested("arena.spectator_mode")) == "true" || strtolower($arena->getNested("arena.spectator_mode")) == "false") && (strtolower($arena->getNested("arena.time")) == "true" || strtolower($arena->getNested("arena.time")) == "day" || strtolower($arena->getNested("arena.time")) == "night" || is_numeric(strtolower($arena->getNested("arena.time")))) && (strtolower($arena->get("enabled")) == "true" || strtolower($arena->get("enabled")) == "false"))){ 
                                                         return false; 
                                                           }
                                                            return true; 
                                                        }
                                                        
                                public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
                                       if(strtolower($cmd->getName()) == "bg"){
                                                if(isset($args[0])){ 
                                                    if($sender instanceof Player){ 
                                                       switch(strtolower($args[0])){ 
                                                        case "lobby": 
                                                         if(!$sender->hasPermission('bg.command.lobby')){ 
                                                          $sender->sendMessage($this->getMsg('has_not_permission')); 
                                                              break; 
                                                             } 
                                                         if($this->getPlayerArena($sender) !== false){ 
                                                          $this->getPlayerArena($sender)->leaveArena($sender); 
                                                            break; 
                                                          } 
                                                           $sender->teleport(new Position($this->cfg->getNested('lobby.x'), $this->cfg->getNested('lobby.y'), $this->cfg->getNested('lobby.z'), $this->getServer()->getLevelByName($this->cfg->getNested('lobby.world')))); 
                                                           $sender->sendMessage($this->getPrefix().$this->getMsg('send_to_main_world')); 
                                                           break; 
                                                      case "set": 
                                                       if(!$sender->hasPermission('bg.command.set')){ 
                                                        $sender->sendMessage($this->getMsg('has_not_permission')); 
                                                        break; 
                                                       } 
                                                       if(!isset($args[1]) || isset($args[2])){ 
                                                       $sender->sendMessage($this->getPrefix().$this->getMsg('set_help')); 
                                                        break; 
                                                      } 
                                                       if(!$this->arenaExist($args[1])){ 
                                                       $sender->sendMessage($this->getPrefix().$this->getMsg('arena_doesnt_exist')); 
                                                         break; 
                                                       } 
                                                       if($this->isArenaSet($args[1])){ 
                                                       $a = $this->ins[$args[1]]; 
                                                       if($a->game !== 0 || count(array_merge($a->ingamep, $a->lobbyp, $a->spec)) > 0){ 
                                                       $sender->sendMessage($this->getPrefix().$this->getMsg('arena_running')); 
                                                         break; 
                                                       } 
                                                        $a->setup = true; 
                                                    } 
                                                       $this->setters[strtolower($sender->getName())]['arena'] = $args[1]; 
                                                       $sender->sendMessage($this->getPrefix().$this->getMsg('enable_setup_mode')); 
                                                         break; 
                                                        case "help": 
                                                        if(!$sender->hasPermission("bg.command.help")){ 
                                                         $sender->sendMessage($this->getMsg('has_not_permission')); 
                                                            break; 
                                                          } 
                                                         $msg = "§9--- §c§lBattlegrounds Help§l§9 ---§r§f"; 
                                                         if($sender->hasPermission('bg.command.lobby')) $msg .= $this->getMsg('lobby'); 
                                                          if($sender->hasPermission('bg.command.leave')) $msg .= $this->getMsg('onleave'); 
                                                           if($sender->hasPermission('bg.command.join')) $msg .= $this->getMsg('onjoin'); 
                                                           if($sender->hasPermission('bg.command.start')) $msg .= $this->getMsg('start'); 
                                                            if($sender->hasPermission('bg.command.stop')) $msg .= $this->getMsg('stop'); 
                                                             if($sender->hasPermission('bg.command.kick')) $msg .= $this->getMsg('kick'); 
                                                            if($sender->hasPermission('bg.command.set')) $msg .= $this->getMsg('set'); 
                                                            if($sender->hasPermission('bg.command.delete')) $msg .= $this->getMsg('delete'); 
                                                            if($sender->hasPermission('bg.command.create')) $msg .= $this->getMsg('create'); 
                                                            $sender->sendMessage($msg); 
                                                               break; 
                                                        case "create": 
                                                          if(!$sender->hasPermission('bg.command.create')){ 
                                                           $sender->sendMessage($this->getMsg('has_not_permission')); 
                                                            break; 
                                                          } 
                                                            if(!isset($args[1]) || isset($args[2])){ 
                                                             $sender->sendMessage($this->getPrefix().$this->getMsg('create_help')); 
                                                            break; 
                                                         } 
                                                            if($this->arenaExist($args[1])){ 
                                                            $sender->sendMessage($this->getPrefix().$this->getMsg('arena_already_exist')); 
                                                            break; 
                                                          } 
                                                            $a = new Config($this->getDataFolder()."arenas/$args[1].yml", Config::YAML); 
                                                            file_put_contents($this->getDataFolder()."arenas/$args[1].yml", $this->getResource('arenas/default.yml')); 
                                                            $this->arenas[$args[1]] = $a->getAll(); 
                                                            $sender->sendMessage($this->getPrefix().$this->getMsg('arena_create')); 
                                                            $name = $args[1]; 
                                                            $this->zipper($sender, $name); 
                                                            break; 
                                                        case "delete": 
                                                        if(!$sender->hasPermission('bg.command.delete')){ 
                                                        $sender->sendMessage($this->getMsg ('has_not_permission')); 
                                                          break; 
                                                        } 
                                                        if(!isset($args[1]) || isset($args[2])){ 
                                                        $sender->sendMessage($this->getPrefix().$this->getMsg('delete_help')); 
                                                        break; 
                                                       } 
                                                        if(!$this->arenaExist($args[1])){ 
                                                        $sender->sendMessage($this->getPrefix().$this->getMsg('arena_doesnt_exist')); 
                                                        break; 
                                                     } 
                                                       unlink($this->getDataFolder()."arenas/$args[1].yml"); 
                                                       unset($this->arenas[$args[1]]); 
                                                       $sender->sendMessage($this->getPrefix().$this->getMsg('arena_delete')); 
                                                 break; 
                                            case "join": if(!$sender->hasPermission('bg.command.join')){ $sender->sendMessage($this->getMsg('has_not_permission')); break; } if(!isset($args[1]) || isset($args[2])){ $sender->sendMessage($this->getPrefix().$this->getMsg('join_help')); break; } if(!$this->arenaExist($args[1])){ $sender->sendMessage($this->getPrefix().$this->getMsg('arena_doesnt_exist')); break; } if($this->arenas[$args[1]]['enable'] === false){ $sender->sendMessage($this->getPrefix().$this->getMsg('arena_doesnt_exist')); break; } if($sender->getPlayer()->getLevel()->getFolderName() != "MainSpawn"){ $sender->sendMessage("Please use this in Lobby"); break; } $this->ins[$args[1]]->joinToArena($sender); break; case "leave": if(!$sender->hasPermission('bg.command.leave')){ $sender->sendMessage($this->getMsg ('has_not_permission')); break; } if(isset($args[1])){ $sender->sendMessage($this->getPrefix().$this->getMsg('leave_help')); break; } if($this->getPlayerArena($sender) === false){ $sender->sendMessage($this->getPrefix().$this->getMsg('use_cmd_in_game')); break; } $this->getPlayerArena($sender)->leaveArena($sender); break; case "start": if(!$sender->hasPermission('bg.command.start')){ $sender->sendMessage($this->plugin->getMsg('has_not_permission')); break; } if(isset($args[2])){ $sender->sendMessage($this->getPrefix().$this->getMsg('start_help')); break; } if(isset($args[1])){ if(!isset($this->ins[$args[1]])){ $sender->sendMessage($this->getPrefix().$this->getMsg('arena_doesnt_exist')); break; } $this->ins[$args[1]]->startGame(); break; } if($this->getPlayerArena($sender) === false){ $sender->sendMessage($this->getPrefix().$this->getMsg('start_help')); break; } $this->getPlayerArena($sender)->startGame(); break; case "stop": if(!$sender->hasPermission('bg.command.stop')){ $sender->sendMessage($this->plugin->getMsg('has_not_permission')); break; } if(isset($args[2])){ $sender->sendMessage($this->getPrefix().$this->getMsg('stop_help')); break; } if(isset($args[1])){ if(!isset($this->ins[$args[1]])){ $sender->sendMessage($this->getPrefix().$this->getMsg('arena_doesnt_exist')); break; } $this->ins[$args[1]]->stopGame(); break; } if($this->getPlayerArena($sender) === false){ $sender->sendMessage($this->getPrefix().$this->getMsg('stop_help')); break; } $this->getPlayerArena($sender)->stopGame(); break; case "kick": if(!$sender->hasPermission('bg.command.kick')){ $sender->sendMessage($this->getMsg('has_not_permission')); break; } if(!isset($args[2]) || isset($args[4])){ $sender->sendMessage($this->getPrefix().$this->getMsg('kick_help')); break; } if(!isset(array_merge($this->ins[$args[1]]->ingamep, $this->ins[$args[1]]->lobbyp, $this->ins[$args[1]]->spec, $this->ins[$args[1]]->zombie)[strtolower($args[2])])){ $sender->sendMessage($this->getPrefix().$this->getMsg('player_not_exist')); break; } if(!isset($args[3])){ $args[3] = ""; } $this->ins[$args[1]]->kickPlayer($args[2], $args[3]); break; case "setlobby": if(!$sender->hasPermission('bg.command.setlobby')){ $sender->sendMessage($this->getMsg('has_not_permission')); break; } if(isset($args[1])){ $sender->sendMessage($this->getPrefix().$this->getMsg('setlobby_help')); break; } $this->setters[strtolower($sender->getName())]['type'] = "mainlobby"; $sender->sendMessage($this->getPrefix().$this->getMsg('break_block')); break; default: $sender->sendMessage($this->getPrefix().$this->getMsg('help')); } return true; } $sender->sendMessage('run command only in-game'); return true; } $sender->sendMessage($this->getPrefix().$this->getMsg('help')); } return true; } public function zipper($sender, $name){ $path = realpath($sender->getServer()->getDataPath() . 'worlds/' . $name); $zip = new \ZipArchive; @mkdir($this->getDataFolder() . 'arenasmap/', 0755); $zip->open($this->getDataFolder() . 'arenasmap/' . $name . '.zip', $zip::CREATE | $zip::OVERWRITE); $files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY ); foreach ($files as $datos) { if (!$datos->isDir()) { $relativePath = $name . '/' . substr($datos, strlen($path) + 1); $zip->addFile($datos, $relativePath); } } $zip->close(); $sender->getServer()->loadLevel($name); unset($zip, $path, $files); } public function arenaExist($name){ if(isset($this->arenas[$name])){ return true; } return false; } public function getMsg($key){ $msg = $this->msg; return str_replace("&", "ÃÂ§", $msg->get($key)); } public function onBlockTouch(PlayerInteractEvent $e){ $p = $e->getPlayer(); $b = $e->getBlock(); if(isset($this->selectors[strtolower($p->getName())])){ $p->sendMessage(TextFormat::BLUE."X: ".TextFormat::GREEN.$b->x.TextFormat::BLUE." Y: ".TextFormat::GREEN.$b->y.TextFormat::BLUE." Z: ".TextFormat::GREEN.$b->z); } } public function getPrefix(){ return str_replace("&", "ÃÂ§", $this->cfg->get('9Prefix')); }


       


