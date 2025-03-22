<?php

namespace App\Service;

use App\Controller\BaseService;
use App\Validation\User\CreateUserValidation;
use App\Validation\User\UpdateUserValidation;
use App\Entity\User;
use App\Entity\UserCode;
use App\Exception\Validation as CustomValidation;
use App\Helper\GeneralHelper;
use App\Service\Common\CollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserService extends BaseService
{
    private UserPasswordHasherInterface $hasher;
    private JWTTokenManagerInterface $JWTTokenManager;
    private MailerInterface $mailer;
    private ParameterBagInterface $parameterBag;

    function __construct(
        EntityManagerInterface $em,
        Security $security,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $JWTTokenManager,
        CollectionService $collectionService,
        MailerInterface $mailer,
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct($em, $security, $validator, $collectionService);
        $this->hasher = $hasher;
        $this->JWTTokenManager = $JWTTokenManager;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    public function get($id): ?User
    {
        return parent::_getObject($id, User::class);
    }

    public function create(array $data, $isRegister = false): User
    {
        $this->_validate(new CreateUserValidation(), $data);
        $this->validateUniqueEmailOrPhone($data['email'], $data['phoneNumber']);

        $obj = new User();
        $obj->fromArray($this->collectionService, $data);
        $obj->setRole('ROLE_USER')
            ->setUsername($this->generateUsername($obj));

        if ($isRegister and $obj->getEmail()) {
            $obj->setIsEnabled(false);
        } else {
            $obj->setIsEnabled(true);
        }


        $obj->setPassword($this->hasher->hashPassword(new User(), $data['password']));

        $this->em->persist($obj);
        $this->em->flush();

        if ($isRegister and $obj->getEmail()) {
            $objUserCode = new UserCode();
            $objUserCode->setUser($obj)
                ->setReason('activation')
                ->setCode(GeneralHelper::getRandomCode(6));

            $appName = $this->parameterBag->get('app_name');

            $email = (new TemplatedEmail())
                ->from(new Address('contact@plugtotech.com', $appName))
                ->to($obj->getEmail())
                ->subject('Activa tu cuenta en ' . $appName)
                ->textTemplate('email/activateUser.txt.twig')
                ->htmlTemplate('email/activateUser.html.twig')
                ->context([
                    'first_name' => $obj->getFirstName(),
                    'code' => $objUserCode->getCode(),
                    'app_name' => $appName
                ]);
            $this->mailer->send($email);

            $this->em->persist($objUserCode);
            $this->em->flush();
        }

        return $obj;
    }

    public function update(string $id, array $data): User {
        $this->_validate(new UpdateUserValidation(), $data);
        $user = $this->get($id);
        $this->validateUniqueEmailOrPhone($data['email'], $data['phoneNumber'], $user->getId());

        $isValidNewPassword = false;
        if (isset($data['password']) and isset($data['oldPassword'])) {
            if ($this->hasher->isPasswordValid($user, $data['oldPassword'])) {
                $isValidNewPassword = true;
                $data['password'] = (string) $data['password'];
            } else {
                $r = [
                    'messages' => [
                        'oldPassword' => 'La contraseña anterior no coincide.'
                    ]
                ];
                throw new CustomValidation(null, 400, $r);
            }
        }

        else {
            unset($data['password']);
        }

        $user->fromArray($this->collectionService, $data);

        if ($isValidNewPassword) {
            $user->setPassword($this->hasher->hashPassword(new User(), $data['password']));
        }

        $this->em->flush();
        return $user;
    }

    public function activate($code): bool
    {
        if ($objUserCode = $this->em->getRepository(UserCode::class)->findOneBy(['reason' => 'activation', 'code' => $code])) {
            $objUserCode->getUser()->setIsEnabled(true);
            $this->em->remove($objUserCode);
            $this->em->flush();
            return true;
        }
        return false;
    }

    public function delete(string $id): bool
    {
        return parent::_deleteObject($id, User::class);
    }

    private function isUsernameAvailable($username): bool
    {
        //$this->em->getFilters()->disable('softdeleteable');
        $r = $this->em->getRepository(User::class)->findOneByUsername($username);
        //$this->em->getFilters()->enable('softdeleteable');
        if ($r)
            return false;
        return true;
    }

    private function generateUsername(User $user): string
    {
        if ($user->getEmail()) {
            $eResult = explode('@', $user->getEmail());
            $username = $eResult[0];
            if ($this->isUsernameAvailable($username))
                return $username;
        }

        $username = strtolower(trim($user->getFirstName()));
        if (!$this->isUsernameAvailable($username)) {
            if ($user->getLastName())
                $username .= strtolower(substr(trim($user->getLastName()), 0, 1));
            if (!$this->isUsernameAvailable($username)) {
                $i = 0;
                do {
                    $i++;
                } while (!$this->isUsernameAvailable($username . $i));
                $username .= $i;
            }
        }
        return $username;
    }

    private function validateUniqueEmailOrPhone($email, $phone, $id = null) {
        $users = $this->em->getRepository(User::class)->getUserByEmailOrPhone($email, $phone);

        $violations = [];

        foreach ($users as $user) {
            if ($email == $user['email'] && (empty($id) || $id !== $user['id'])) {
                $violations['email'] = [
                    "Correo electrónico ya existe"
                ];
            }

            if ($phone == $user['phone_number'] && (empty($id) || $id !== $user['id'])) {
                $violations['phoneNumber'] = [
                    "Número de teléfono ya existe"
                ];
            }
        }

        if (!empty($violations) ) {
            $r = [
                'messages' => $violations
            ];
            throw new CustomValidation(null, 400, $r);
        }
    }

}
