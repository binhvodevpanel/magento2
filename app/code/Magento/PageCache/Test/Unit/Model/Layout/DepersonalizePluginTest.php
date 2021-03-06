<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Layout;

use Magento\Framework\Event\Manager;
use Magento\Framework\Message\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use Magento\PageCache\Model\Layout\DepersonalizePlugin;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Tests \Magento\PageCache\Model\Layout\DepersonalizePlugin.
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var LayoutInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var Manager|PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var Session|PHPUnit_Framework_MockObject_MockObject
     */
    private $messageSessionMock;

    /**
     * @var DepersonalizeChecker|PHPUnit_Framework_MockObject_MockObject
     */
    private $depersonalizeCheckerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->eventManagerMock = $this->createMock(Manager::class);
        $this->messageSessionMock = $this->createPartialMock(
            Session::class,
            ['clearStorage']
        );
        $this->depersonalizeCheckerMock = $this->createMock(DepersonalizeChecker::class);
        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            DepersonalizePlugin::class,
            [
                'depersonalizeChecker' => $this->depersonalizeCheckerMock,
                'eventManager' => $this->eventManagerMock,
                'messageSession' => $this->messageSessionMock,
            ]
        );
    }

    /**
     * Test afterGenerateElements method when depersonalization is needed.
     *
     * @return void
     */
    public function testAfterGenerateElements(): void
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('depersonalize_clear_session'));
        $this->messageSessionMock->expects($this->once())->method('clearStorage');
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);

        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * Test afterGenerateElements method when depersonalization is not needed.
     *
     * @return void
     */
    public function testAfterGenerateElementsNoDepersonalize(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->messageSessionMock->expects($this->never())->method('clearStorage');

        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }
}
