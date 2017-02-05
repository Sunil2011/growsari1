<?php

namespace Api\Service;

use Api\Exception\ApiException;
use Api\Service\BaseService;
use Api\Table\AccountTable;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Validator\EmailAddress;

class CreateAccountService extends BaseService
{

    public function create($name, $email, $password, $username = null, $phone = null, $type = null, $role = 'USER')
    {
        $data = array(
            'email' => $email,
            'password' => $password,
            'passwordVerify' => $password,
            'username' => ($username) ? $username : $email,
            'phone' => $phone,
            'displayName' => $name,
            'state' => 1,
            'type' => $type,
            'role' => $role,
        );

        // Validate the data
        $this->validateInput($data);

        $entityClass = $this->getServiceLocator()->get('zfcuser_module_options')->getUserEntityClass();
        $form = $this->getServiceLocator()->get('zfcuser_register_form');
        $formHydrator = $this->getServiceLocator()->get('zfcuser_user_hydrator');

        $form->setHydrator($formHydrator);
        $form->bind(new $entityClass);
        $form->setData($data);

        if (!$form->isValid()) {
            return $this->error("Please fill valid details", array('errors' => $form->getMessages()), 400);
        }

        // Create an account
        $account = $form->getData();
        $account->setCreatedAt(new Expression('NOW()'));
        $account->setUpdatedAt(new Expression('NOW()'));
        $account->setDisplayName($data['displayName']);
        $account->setUsername($data['username']);
        $account->setPhone($data['phone']);
        $account->setType($data['type']);
        $account->setRole($data['role']);
        $account->setState(1);
        
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(14);
        $account->setPassword($bcrypt->create($account->getPassword()));

        $this->getServiceLocator()->get('zfcuser_user_mapper')->insert($account);
        if (!$account->getId()) {
            throw new ApiException("Unable to create account, please try again", 500);
        }

        return (int) $account->getId();
    }

    private function validateInput($data)
    {
        // validate user account exists
        $validator = new EmailAddress();
        if ($validator->isValid($data['email'])) {
            $accountObj = $this->getServiceLocator()->get('zfcuser_user_mapper')->findByEmail($data['email']);
        } else {
            $accountObj = $this->getServiceLocator()->get('zfcuser_user_mapper')->findByUsername($data['username']);
        }

        if ($accountObj) {
            throw new ApiException("User with this Username/Email already exists!", 400);
        }

        // validate the type
        $types = array(
            AccountTable::TYPE_GROWSARI,
            AccountTable::TYPE_SALESPERSON,
            AccountTable::TYPE_SHIPPER,
            AccountTable::TYPE_STORE,
            AccountTable::TYPE_WAREHOUSE,
            AccountTable::TYPE_CALLCENTER,
        );
        if (!in_array($data['type'], $types)) {
            throw new ApiException("Please provide valid user type", 400);
        }
    }

}
