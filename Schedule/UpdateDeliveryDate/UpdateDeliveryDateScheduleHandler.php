<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Schedule\UpdateDeliveryDate;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Orders\Messenger\Schedules\CancelOrders\CancelOzonOrdersScheduleMessage;
use BaksDev\Ozon\Orders\Schedule\CancelOrders\CancelOrdersScheduleMessage;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Users\Profile\UserProfile\Messenger\Scheduler\UpdateDeliveryDateMessage;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion\UserProfileByRegionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateDeliveryDateScheduleHandler
{
    public function __construct(
        private UserProfileByRegionInterface $allProfileToken,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    public function __invoke(UpdateDeliveryDateScheduleMessage $message): void
    {
        /** Получаем активные токены авторизации профилей */
        $profiles = $this->allProfileToken
            ->onlyCurrentRegion()
            ->findAll();

        if(false === $profiles || false === $profiles->valid())
        {
            return;
        }

        foreach($profiles as $UserProfileByRegionResult)
        {
            $this->messageDispatch->dispatch(
                message: new UpdateDeliveryDateMessage($UserProfileByRegionResult->getId()),
                stamps: [new MessageDelay('15 seconds')],
                transport: (string) $UserProfileByRegionResult->getId(),
            );
        }
    }
}