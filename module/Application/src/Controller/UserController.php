<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Application\Service;
use Application\TableGateway;
use Application\Service\MailService;

class UserController extends AbstractController
{
    public function profileAction()
    {
        $user = $this->getUser();
        $form = new Form\Profile('uploader', ['userId' => $user->id]);
        $tempFile = null;

        $prg = $this->fileprg($form);
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response) {
            return $prg; // Return PRG redirect response
        } elseif (is_array($prg)) {
            if ($form->isValid()) {
                $data = $form->getData();
                // Form is valid, save the form!
            } else {
                // Form not valid, but file uploads might be valid...
                // Get the temporary file information to show the user in the view
                $fileErrors = $form->get('image-file')->getMessages();
                if (empty($fileErrors)) {
                    $tempFile = $form->get('image-file')->getValue();
                }
            }
        }

        $groupTable = $this->get(TableGateway\Group::class);
        $groups     = $this->getUserGroups();

        $count['total'] = $this->disponibilityTable->count(['userId' => $user->id]);
        $eventsOk = $this->disponibilityTable->fetchAll(['userId' => $user->id, 'response' => \Application\Model\Disponibility::RESP_OK]);

        $count['match'] = 0;
        foreach ($eventsOk as $event) {
            if ($this->statsTable->count(['eventId' => $event->id])) $count['match']++;
        }
        $count['ok'] = floor(count($eventsOk) * 100 / $count['total']);
        $count['no'] = floor($this->disponibilityTable->count(['userId' => $user->id, 'response' => \Application\Model\Disponibility::RESP_NO]) * 100 / $count['total']);

        $this->layout()->setTemplate('layout/no-title.phtml');
        $this->layout()->user = $this->getUser();

        return new ViewModel([
            'user'  => $this->getUser(),
            'form'  => $form,
            'count' => $count,
        ]);
    }
}