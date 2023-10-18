<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Command\Upgrade;

use BaksDev\Auth\Email\Repository\AccountEventActiveByEmail\AccountEventActiveByEmailInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Core\Command\Update\ProjectUpgradeInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\ExistUserProfileByUser\ExistUserProfileByUserInterface;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Info\InfoDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileHandler;
use DomainException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'baks:users-profile-user:admin',
    description: 'Добавляет администратору ресурса профиль пользователя',
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradeUserProfileAdminCommand extends Command implements ProjectUpgradeInterface
{

    private string $HOST;
    private AccountEventActiveByEmailInterface $accountEventActiveByEmail;
    private ExistUserProfileByUserInterface $existUserProfileByUser;
    private UserProfileHandler $userProfileHandler;

    public function __construct(
        #[Autowire(env: 'HOST')] string $HOST,
        AccountEventActiveByEmailInterface $accountEventActiveByEmail,
        ExistUserProfileByUserInterface $existUserProfileByUser,
        UserProfileHandler $userProfileHandler
    )
    {
        parent::__construct();

        $this->HOST = $HOST;
        $this->accountEventActiveByEmail = $accountEventActiveByEmail;
        $this->existUserProfileByUser = $existUserProfileByUser;
        $this->userProfileHandler = $userProfileHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $AccountEmail = new  AccountEmail('admin@'.$this->HOST);

        $AccountEvent = $this
            ->accountEventActiveByEmail
            ->getAccountEvent($AccountEmail);

        if($AccountEvent)
        {
            /**
             * Проверяем, имеется ли у администратора ресурса профиль пользователя
             */
            $exist = $this
                ->existUserProfileByUser
                ->isExistsProfile($AccountEvent->getAccount());

            if(!$exist)
            {
                $io = new SymfonyStyle($input, $output);
                $io->text('Добавляем администратору ресурса профиль пользователя');

                /**
                 * Создаем профиль пользователя по умолчанию
                 */
                $UserProfileDTO = new UserProfileDTO();
                $UserProfileDTO->setSort(100);
                $UserProfileDTO->setType(TypeProfileUid::userProfileType());

                /** @var InfoDTO $InfoDTO */
                $InfoDTO = $UserProfileDTO->getInfo();
                $InfoDTO->setUrl(uniqid('', false));
                $InfoDTO->setUsr($AccountEvent->getAccount());
                $InfoDTO->setStatus(new UserProfileStatus(UserProfileStatusActive::class));

                $PersonalDTO = $UserProfileDTO->getPersonal();
                $PersonalDTO->setUsername($AccountEmail->getUserName());

                $UserProfile = $this->userProfileHandler->handle($UserProfileDTO);

                if(!$UserProfile instanceof UserProfile)
                {
                    throw new DomainException(sprintf('%s: Ошибка при добавлении профиля администратору ресурса', $UserProfile));
                }
            }
        }

        return Command::SUCCESS;
    }

    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 99;
    }
}
