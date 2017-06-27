<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;

class indexActionTest extends \PHPUnit_Framework_TestCase
{

    const SELENIUM_SERVER_HOST = "http://localhost:4444/wd/hub";
    protected $url = 'http://localhost:8888';
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

        file_put_contents(__DIR__ . '/../../dat/log.json', json_encode([]));
    }

    public function testBBSHome()
    {
        $this->webDriver->get($this->url);
        $this->assertContains('掲示板', $this->webDriver->getTitle());
    }

    public function testHomeLink()
    {
        $this->webDriver->get($this->url);
        $this->webDriver->findElement(WebDriverBy::partialLinkText('Home'))->click();
        $this->webDriver->wait(10,500)->until(
            WebDriverExpectedCondition::titleIs('掲示板')
        );
        $this->assertContains('掲示板', $this->webDriver->getTitle());
    }

    public function testReload()
    {
        $this->webDriver->get($this->url);

        $this->webDriver->findElement(WebDriverBy::partialLinkText('リロード'))->click();
        $this->webDriver->wait(10,500)->until(
            WebDriverExpectedCondition::titleIs('掲示板')
        );

        $this->assertContains('掲示板', $this->webDriver->getTitle());
    }

    public function testWrite()
    {
        $this->webDriver->get($this->url);

        $this->webDriver->wait(10,500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('input[type=submit]'))
        );

        $this->webDriver->findElement(WebDriverBy::cssSelector('textarea[name=body]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('掲示板のテスト');
        $this->webDriver->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        $this->assertContains('掲示板', $this->webDriver->getTitle());
        $element = $this->webDriver->findElements(WebDriverBy::cssSelector('div.post'));
        $this->assertCount(1, $element);
    }

    public function testDelete()
    {
        $this->webDriver->get($this->url);

        $this->webDriver->wait(10,500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('input[type=submit]'))
        );

        $this->webDriver->findElement(WebDriverBy::cssSelector('textarea[name=body]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('掲示板のテスト');
        $this->webDriver->findElement(WebDriverBy::cssSelector('input[name=del_pass]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('1234');
        $this->webDriver->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        $this->webDriver->wait(10,500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('input[type=submit]'))
        );

        $element = $this->webDriver->findElements(WebDriverBy::cssSelector('div.del_form'));
        $this->assertCount(1, $element);

        $this->webDriver->executeScript("document.getElementsByTagName('a')[3].click()");

        $this->webDriver->findElement(WebDriverBy::cssSelector('div.del_form input[name=del_pass]'))->click();
        $this->webDriver->getKeyboard()->sendKeys('1234');
        $this->webDriver->findElement(WebDriverBy::cssSelector('div.del_form input[type=submit]'))->click();

        $element = $this->webDriver->findElements(WebDriverBy::cssSelector('div.del_form'));
        $this->assertCount(0, $element);
    }
}