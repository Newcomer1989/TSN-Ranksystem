<?PHP
/**
 * @file
 * TeamSpeak 3 PHP Framework
 *
 * $Id: Exception.php 10/11/2013 11:35:21 scp@orilla $
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   TeamSpeak3
 * @version   1.1.23
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */
class TeamSpeak3_Exception extends Exception{ protected static $messages=array(); public function __construct($mesg,$code=0x00){parent::__construct($mesg,$code);if(array_key_exists((int)$code,self::$messages)){$this->message=$this->prepareCustomMessage(self::$messages[intval($code)]);}TeamSpeak3_Helper_Signal::getInstance()->emit("errorException",$this);} protected function prepareCustomMessage(TeamSpeak3_Helper_String$mesg){$args=array("code"=>$this->getCode(),"mesg"=>$this->getMessage(),"line"=>$this->getLine(),"file"=>$this->getFile(),);return $mesg->arg($args)->toString();} public static function registerCustomMessage($code,$mesg){if(array_key_exists((int)$code,self::$messages)){ throw new self("custom message for code 0x".strtoupper(dechex($code))." is already registered");}if(!is_string($mesg)){ throw new self("custom message for code 0x".strtoupper(dechex($code))." must be a string");}self::$messages[(int)$code]=new TeamSpeak3_Helper_String($mesg);} public static function unregisterCustomMessage($code){if(!array_key_exists((int)$code,self::$messages)){ throw new self("custom message for code 0x".strtoupper(dechex($code))." is not registered");}unset(self::$messages[intval($code)]);} public function getSender(){$trace=$this->getTrace();return (isset($trace[0]["class"]))?$trace[0]["class"]:"{main}";}}?>