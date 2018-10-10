<?php
namespace Application\Controller;

use RuntimeException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\Adapter;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Math\Rand;
use Zend\Crypt\Password\Bcrypt;
use Application\TableGateway;
use Application\Model;
use Application\Service;
use Zend\Console\Console;
use Zend\Console\Exception\RuntimeException as ConsoleException;
use Zend\Console\ColorInterface as Color;
use Application\Service\MailService;
use Application\Service\OneSignalService;

class ConsoleController extends AbstractController
{
    protected $_users = [];

    public function cleanStatsAction()
    {
        $stats = $this->statsTable->fetchAll([
            'pointFor' => \Application\Model\Stats::POINT_US,
            'reason' => [
                \Application\Model\Stats::FAULT_ATTACK,
                \Application\Model\Stats::FAULT_DEFENCE,
                \Application\Model\Stats::FAULT_SERVE,
            ],
            'userId <> ?' => null,
        ]);

        \Zend\Debug\Debug::dump($stats->toArray());die;

    }

    public function init()
    {
        $config = $this->get('config');

        $this->adapter = new Adapter([
            'driver'   => 'Pdo_Mysql',
            'username' => $config['db']['username'],
            'password' => $config['db']['password'],
            'database' => $config['db']['migration']['old-db'],
            'driver_options' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
            ],
        ]);

        $this->newAdapter = new Adapter([
            'driver'   => 'Pdo_Mysql',
            'username' => $config['db']['username'],
            'password' => $config['db']['password'],
            'database' => $config['db']['migration']['current'],
            'driver_options' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
            ],
        ]);
    }

    public function migrationAction()
    {
        $console = Console::getInstance();

        $this->init();
        $console->writeLine('Working on events ...', Color::BLUE);
        $this->events();
        $console->writeLine('DONE', Color::GREEN);
    }

    public function events()
    {

        $matchs = $this->newAdapter->query('SELECT * FROM `match` WHERE `set1Team1` IS NOT NULL AND `debrief` IS NOT NULL')->execute();

        foreach ($matchs as $match) {
            $sets = [
                $match['set1Team1'] . '-' . $match['set1Team2'],
                $match['set2Team1'] . '-' . $match['set2Team2'],
                $match['set3Team1'] . '-' . $match['set3Team2'],
            ];
            if ($match['set4Team1']) $sets[] = $match['set4Team1'] . '-' . $match['set4Team2'];
            if ($match['set5Team1']) $sets[] = $match['set5Team1'] . '-' . $match['set5Team2'];
            $sets = json_encode($sets);
            $this->newAdapter->query('UPDATE `event` SET `sets` = \'' . $sets . '\', `victory` = "' . $match['victory'] . '", `score` = "' . $match['sets'] . '" , `debrief` = "' . addslashes($match['debrief']) . '" WHERE id = "' . $match['eventId'] . '"')->execute();
        }
    }

    public function groups()
    {
        $this->newAdapter->query('TRUNCATE TABLE `group`')->execute();
        $this->newAdapter->query('TRUNCATE TABLE `userGroup`')->execute();

        // Groups
        $data = $this->adapter->query('SELECT * FROM `group`')->execute();
        $values = '';
        $userValues = '';
        $this->_userGroups = [];
        foreach ($data as $group) {
            $group['brand'] = Model\Group::initBrand($group['name']);
            if (!isset($group['description'])) $group['description'] = '';
            $userIds = json_decode($group['userIds']);
            foreach ($userIds as $userId) {
                $this->_userGroups[$group['id']][] = $userId;
                $isAdmin = (in_array(json_decode($group['adminIds']), $userIds) || ($userId == $group['userId'])) ? 1 : 0;
                $userValues .= '("' . $userId . '", "' . $group['id'] . '", "' . $isAdmin . '"),';
            }
            $values .= '("' . $group['id'] . '", "' . $group['name'] . '",  "' . $group['brand'] . '",  "' . $group['description'] . '"),';
        }
        $values = substr($values, 0, -1);
        $userValues = substr($userValues, 0, -1);

        $values .= ';';

        $this->newAdapter->query('INSERT INTO `group` (`id`, `name`, `brand`, `description`) VALUES ' . $values)->execute();
        $this->newAdapter->query('INSERT INTO `userGroup` (`userId`, `groupId`, `admin`) VALUES ' . $userValues . ' ON DUPLICATE KEY UPDATE userId=userId')->execute();
    }

    public function users()
    {
        $this->newAdapter->query('TRUNCATE TABLE `user`')->execute();

        $users = $this->adapter->query('SELECT * FROM `user`')->execute();
        foreach ($users as $data) {
            $bCrypt = new Bcrypt();
            $this->_users[$data['id']] = $data;
            $this->_users[$data['id']]['status']   = Model\User::CONFIRMED;
            $this->_users[$data['id']]['password'] = $bCrypt->create($data['password']);
            $this->_users[$data['id']]['display']  = Model\User::DISPLAY_LARGE;
        }

        // insert users
        $values = '';
        foreach ($this->_users as $key => $data) {
            $values .= '("' . implode('","', $data) . '"),';
        }
        $values = substr($values, 0, -1);
        $values .= ';';
        $this->newAdapter->query('INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `password`, `status`, `display`) VALUES ' . $values)->execute();
    }

    public function places()
    {
        $this->newAdapter->query('TRUNCATE TABLE `place`')->execute();

        $data = $this->adapter->query('SELECT * FROM `place`')->execute();
        $this->places = [];
        foreach ($data as $place) {
            $this->places[$place['id']] = $place;
        }
    }

    public function disponibility()
    {
        $this->newAdapter->query('TRUNCATE TABLE `disponibility`')->execute();

        $guests = $this->adapter->query('SELECT * FROM `guest`')->execute();

        // insert users
        $values = '';
        foreach ($guests as $guest) {
            if (($guest['date'] > date('Y-m-d H:i:s', time())) && !in_array($guest['userId'], $this->_userGroups[$guest['groupId']])) {
                $console = Console::getInstance();
                $console->writeLine('User ' . $guest['userId'] . ' no more in group ' . $guest['groupId'], Color::RED);
                continue;
            }
            unset($guest['date']);
            $values .= '("' . implode('","', $guest) . '"),';
        }
        $values = substr($values, 0, -1);

        $this->newAdapter->query('INSERT INTO `disponibility` VALUES ' . $values . ' ON DUPLICATE KEY UPDATE eventId=eventId;')->execute();
    }

    public function comments()
    {
        $this->newAdapter->query('TRUNCATE TABLE `comment`')->execute();

        $data = $this->adapter->query('SELECT * FROM `comment`')->execute();

        // insert users
        $values = '';
        foreach ($data as $row) {
            $values .= '("' . $row['id'] . '", "' . $row['userId'] . '",  "' . $row['eventId'] . '",  "' . addslashes($row['comment']) . '",  "' . $row['date'] . '"),';
        }
        $values = substr($values, 0, -1);
        $values .= ';';

        $this->newAdapter->query('INSERT INTO `comment` VALUES ' . $values)->execute();
    }

    public function trainings()
    {
        $this->newAdapter->query('TRUNCATE TABLE `training`')->execute();

        // Groups
        $data = $this->adapter->query('SELECT * FROM `training`')->execute();
        $values = '';
        foreach ($data as $training) {
            $values .= '("' . $training['id'] . '", "' . $training['status'] . '",  "' . $training['groupId'] . '",  "' . $training['name'] . '",  "' . $training['sendDay'] . '",  "' . $training['day'] . '",  "' . $training['time'] . '" ,  "' . $this->places[$training['placeId']]['name'] . '" ,  "' . $this->places[$training['placeId']]['address'] . '" ,  "' . $this->places[$training['placeId']]['city'] . '" ,  "' . $this->places[$training['placeId']]['zipCode'] . '"),';
        }
        $values = substr($values, 0, -1);
        $values .= ';';

        $this->newAdapter->query('INSERT INTO `training` VALUES ' . $values)->execute();
    }

    public function notifs()
    {
        $this->newAdapter->query('TRUNCATE TABLE `notification`')->execute();

        $data = $this->adapter->query('SELECT * FROM `notification`')->execute();

        // insert users
        $values = '';
        foreach ($data as $row) {
            $values .= '("' . implode('","', $row) . '"),';
        }
        $values = substr($values, 0, -1);
        $values .= ';';

        $this->newAdapter->query('INSERT INTO `notification` VALUES ' . $values)->execute();
    }

    public function recurentAction()
    {
        date_default_timezone_set('Europe/Paris');
        $console        = Console::getInstance();

        $trainings      = $this->trainingTable->fetchAll([
            'emailDay' => date('l'),
            'status'   => \Application\Model\Training::ACTIVE
        ]);

        $groups = [];
        foreach ($trainings as $training) {
            if (!isset($groups[$training->groupId])) {
                $group = $this->groupTable->find($training->groupId);
                $groups[$training->groupId] = $group;
            } else {
                $group = $groups[$training->groupId];
            }

            $userIds = $this->userGroupTable->getUserIds($group->id);
            $users = $this->userTable->fetchAll([
                'id' => $userIds
            ]);

            $date = new \DateTime('now');
            $date = $date->modify('next ' . strtolower($training->eventDay) . ' ' . $training->time);
            $date = $date->format('Y-m-d H:i:s');

            // Create Event
            $params = [
                'date'    => $date,
                'name'    => $training->name,
                'groupId' => $training->groupId,
                'place'   => $training->place,
                'address' => $training->address,
                'zipCode' => $training->zipCode,
                'city'    => $training->city,
            ];

            try {
                $mapService = $this->get(Service\Map::class);
                $address = $training->address . ', ' . $training->zipCode . ' ' . $training->city . ' France';

                if ($coords = $mapService->getCoordinates($address)) {
                    $params = array_merge($params, $coords);
                }
            } catch (RuntimeException $e) {
                $console->writeLine($e->getMessage(), Color::RED);
            }

            $event = $this->eventTable->save($params);

            foreach ($userIds as $id) {
                $absent = $this->holidayTable->fetchOne([
                    '`from` < ?' => $date,
                    '`to` > ?'   => $date,
                    'userId = ?' => $id
                ]);

                $response = ($absent) ? Model\Disponibility::RESP_NO : Model\Disponibility::RESP_NO_ANSWER;

                $guest = $this->disponibilityTable->save([
                    'eventId'  => $event->id,
                    'userId'   => $id,
                    'response' => $response,
                    'groupId'  => $group->id,
                ]);
            }

            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);
            $config = $this->get('config');
            if ($config['mail']['allowed']) {
                $view       = new \Zend\View\Renderer\PhpRenderer();
                $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
                $resolver->setMap([
                    'event' => __DIR__ . '/../../view/mail/event.phtml'
                ]);
                $view->setResolver($resolver);

                $viewModel  = new ViewModel();
                $viewModel->setTemplate('event')->setVariables(array(
                    'event'     => $event,
                    'group'     => $group,
                    'date'      => $date,
                    'baseUrl'   => $config['baseUrl']
                ));

                $mail = $this->get(MailService::class);
                // $mail->addIcalEvent($event);

                $oneSignal = $this->get(OneSignalService::class);
                $oneSignal->setData([
                    'header'   => 'Nouvel entrainement',
                    'content'  => $event->name,
                    'subtitle' => \Application\Service\Date::toFr($date->format('l d F \à H\hi')),
                    'url'      => $config['baseUrl'] . '/event/detail/' . $event->id,
                ]);

                // send emails
                foreach ($users as $user) {
                    $oneSignal->sendTo($user->email);
                    $mail->addBcc($user->email);
                }
                $mail->setSubject('[' . $group->name . '] ' . $event->name . ' - ' . \Application\Service\Date::toFr($date->format('l d F \à H\hi')));
                $mail->setBody($view->render($viewModel));
                try {
                    $mail->send();
                    // reset bcc emails
                    $mail->setBcc([]);
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function reminderAction()
    {
        $config = $this->get('config');
        $now = new \DateTime('now');
        $now->modify('+ 1 days');
        $events = $this->eventTable->fetchAll([
            'date > ?' => $now->modify('00:00:00')->format('Y-m-d H:i:s'),
            'date < ?' => $now->modify('23:59:59')->format('Y-m-d H:i:s'),
        ]);

        foreach ($events as $event) {
            $emails = [];
            $group  = $this->groupTable->find($event->groupId);
            $disponibilities = $this->disponibilityTable->fetchAll([
                'eventId' => $event->id,
                'response' => [
                    Model\Disponibility::RESP_NO_ANSWER,
                    Model\Disponibility::RESP_UNCERTAIN
                ]
            ]);

            foreach ($disponibilities as $disponibility) {
                $user = $this->userTable->find($disponibility->userId);
                $emails[] = $user->email;
            }

            if ($config['mail']['allowed']) {
                $view       = new \Zend\View\Renderer\PhpRenderer();
                $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
                $resolver->setMap([
                    'reminder' => __DIR__ . '/../../view/mail/reminder.phtml'
                ]);
                $view->setResolver($resolver);

                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);

                $viewModel  = new ViewModel();
                $viewModel->setTemplate('reminder')->setVariables(array(
                    'event'     => $event,
                    'group'     => $group,
                    'date'      => $date,
                    'baseUrl'   => $config['baseUrl']
                ));

                $mail = $this->get(MailService::class);
                $mail->addBcc($emails);
                $mail->setSubject('[' . $group->name . '] ' . $event->name . ' - ' . \Application\Service\Date::toFr($date->format('l d F \à H\hi')));
                $mail->setBody($view->render($viewModel));
                try {
                    $mail->send();
                    // reset bcc emails
                    $mail->setBcc([]);
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function doNotForgetAction()
    {
        $config = $this->get('config');
        $now = new \DateTime('now');
        $now->modify('+ 3 days');
        $events = $this->eventTable->fetchAll([
            'date > ?' => $now->modify('00:00:00')->format('Y-m-d H:i:s'),
            'date < ?' => $now->modify('23:59:59')->format('Y-m-d H:i:s'),
            'reminder' => 1,
        ]);

        foreach ($events as $event) {
            $emails = [];
            $group  = $this->groupTable->find($event->groupId);
            $disponibilities = $this->disponibilityTable->fetchAll([
                'eventId' => $event->id,
            ]);

            foreach ($disponibilities as $disponibility) {
                $user = $this->userTable->find($disponibility->userId);
                $emails[] = $user->email;
            }

            if ($config['mail']['allowed']) {
                $view       = new \Zend\View\Renderer\PhpRenderer();
                $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
                $resolver->setMap([
                    'reminder' => __DIR__ . '/../../view/mail/do-not-forget.phtml'
                ]);
                $view->setResolver($resolver);

                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $event->date);

                $viewModel  = new ViewModel();
                $viewModel->setTemplate('reminder')->setVariables(array(
                    'event'     => $event,
                    'group'     => $group,
                    'date'      => $date,
                    'baseUrl'   => $config['baseUrl']
                ));

                $mail = $this->get(MailService::class);
                $mail->addBcc($emails);
                $mail->setSubject('[' . $group->name . '] ' . $event->name . ' - ' . \Application\Service\Date::toFr($date->format('l d F \à H\hi')));
                $mail->setBody($view->render($viewModel));
                try {
                    $mail->send();
                    // reset bcc emails
                    $mail->setBcc([]);
                } catch (\Exception $e) {
                }
            }
        }
    }
}