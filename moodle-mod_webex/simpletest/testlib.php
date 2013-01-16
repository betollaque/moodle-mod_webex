<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for some mod webex lib stuff.
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/webex/locallib.php');


/**
 * @copyright  2011 petr Skoda
 */
class webex_lib_test extends UnitTestCase {

    public function test_webex_appears_valid_webex() {

        $this->assertTrue(webex_appears_valid_webex('http://example'));
        $this->assertTrue(webex_appears_valid_webex('http://www.example.com'));
        $this->assertTrue(webex_appears_valid_webex('http://www.exa-mple2.com'));
        $this->assertTrue(webex_appears_valid_webex('http://www.example.com/~nobody/index.html'));
        $this->assertTrue(webex_appears_valid_webex('http://www.example.com#hmm'));
        $this->assertTrue(webex_appears_valid_webex('http://www.example.com/#hmm'));
        $this->assertTrue(webex_appears_valid_webex('http://www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(webex_appears_valid_webex('http://www.example.com/žlutý koníček/lala.txt#hmmmm'));
        $this->assertTrue(webex_appears_valid_webex('http://www.example.com/index.php?xx=yy&zz=aa'));
        $this->assertTrue(webex_appears_valid_webex('https://user:password@www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(webex_appears_valid_webex('ftp://user:password@www.example.com/žlutý koníček/lala.txt'));

        $this->assertFalse(webex_appears_valid_webex('http:example.com'));
        $this->assertFalse(webex_appears_valid_webex('http:/example.com'));
        $this->assertFalse(webex_appears_valid_webex('http://'));
        $this->assertFalse(webex_appears_valid_webex('http://www.exa mple.com'));
        $this->assertFalse(webex_appears_valid_webex('http://www.examplé.com'));
        $this->assertFalse(webex_appears_valid_webex('http://@www.example.com'));
        $this->assertFalse(webex_appears_valid_webex('http://user:@www.example.com'));

        $this->assertTrue(webex_appears_valid_webex('lalala://@:@/'));
    }
}