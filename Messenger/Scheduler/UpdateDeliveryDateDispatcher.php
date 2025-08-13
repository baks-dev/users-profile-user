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

namespace BaksDev\Users\Profile\UserProfile\Messenger\Scheduler;


use BaksDev\Users\Profile\UserProfile\Entity\Event\Delivery\UserProfileDelivery;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileDelivery\UserProfileDeliveryInterface;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\UpdateDelivery\UserProfileDeliveryDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\UpdateDelivery\UserProfileDeliveryHandler;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляет дату последней поставки
 */
#[AsMessageHandler(priority: 0)]
final readonly class UpdateDeliveryDateDispatcher
{
    public function __construct(
        private UserProfileDeliveryInterface $UserProfileDeliveryRepository,
        private UserProfileDeliveryHandler $UserProfileDeliveryHandler
    ) {}

    public function __invoke(UpdateDeliveryDateMessage $message): void
    {
        $UserProfileDelivery = $this->UserProfileDeliveryRepository
            ->forProfile($message->getProfile())
            ->find();

        if(false === ($UserProfileDelivery instanceof UserProfileDelivery))
        {
            return;
        }

        $LasDateProfileDelivery = $UserProfileDelivery->getValue()
            ->add(DateInterval::createFromDateString(
                sprintf('%s days', $UserProfileDelivery->getDay()),
            ));


        if($LasDateProfileDelivery > (new DateTimeImmutable()))
        {
            return;
        }

        $UserProfileDeliveryDTO = new UserProfileDeliveryDTO();
        $UserProfileDeliveryDTO->setEvent($UserProfileDelivery->getEventUid());

        $this->UserProfileDeliveryHandler->handle($UserProfileDeliveryDTO);

    }
}
