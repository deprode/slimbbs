<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;

class AuthActionTest extends \PHPUnit_Framework_TestCase
{

    const SELENIUM_SERVER_HOST = "http://localhost:4444/wd/hub";
    protected $url = 'http://localhost:8888/auth/';
    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    public function setUp()
    {
        $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
        $this->webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
    }

    public function tearDown()
    {
        $this->webDriver->quit();
    }

    public function testAuth()
    {
        $this->webDriver->get($this->url);
        $this->assertContains('認証 | 掲示板', $this->webDriver->getTitle());

        $this->webDriver->findElement(WebDriverBy::cssSelector('input[name=id]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('admin');
        $this->webDriver->findElement(WebDriverBy::cssSelector('input[name=password]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('password');
        $this->webDriver->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        $this->webDriver->wait(10,500)->until(
            WebDriverExpectedCondition::titleIs('管理 | 掲示板')
        );
        $this->assertEquals($this->webDriver->getCurrentURL(), 'http://localhost:8888/admin/');
    }

    public function testAuthFailed()
    {
        $this->webDriver->get($this->url);
        $this->assertContains('認証 | 掲示板', $this->webDriver->getTitle());

        $this->webDriver->findElement(WebDriverBy::cssSelector('input[name=id]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('xxxxx');
        $this->webDriver->findElement(WebDriverBy::cssSelector('input[name=password]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('xxxxx');
        $this->webDriver->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        $this->webDriver->wait(10,500)->until(
            WebDriverExpectedCondition::titleIs('認証 | 掲示板')
        );
        $this->assertEquals($this->webDriver->getCurrentURL(), $this->url);

        $errorMessage = $this->webDriver->findElement(WebDriverBy::cssSelector('body > div > div:nth-child(3) > b > u'));
        $this->assertEquals($errorMessage->getText(), 'IDかパスワードが間違っています。');
    }
}