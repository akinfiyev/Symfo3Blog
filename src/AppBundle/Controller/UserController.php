<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\User\EditProfileType;
use AppBundle\Form\User\RegistrationType;
use AppBundle\Service\UploaderService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/registration", name="user_registration")
     */
    public function registrationAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('default');
        }

        return $this->render('@App/user/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/edit", methods={"GET", "POST"}, name="user_profile_edit")
     */
    public function editUserProfileAction(Request $request, UploaderService $uploaderService)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        $avatar = $user->getAvatar();

        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($user->getAvatar())) {
                $avatar = $uploaderService->uploadAvatar(new UploadedFile($user->getAvatar(), 'avatar'));
            }
            $user->setAvatar($avatar);

            $em->flush();

            return $this->render('@App/user/edit_profile.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
                'message' => 'success'
            ]);
        }

        $this->getUser()->setAvatar($avatar);

        return $this->render('@App/user/edit_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/edit/role_blogger_request", name="user_role_blogger_request")
     */
    public function roleBloggerRequestAction(Request $request)
    {
        $this->getUser()->setHasRequestBloggerRole(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($request
            ->headers
            ->get('referer'));
    }

    public function showUserProfileSidebarAction()
    {
        return $this->render('@App/base/sidebar/user/profile.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
