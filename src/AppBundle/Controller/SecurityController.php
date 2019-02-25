<?php


namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Form\Security\ForgetPasswordType;
use AppBundle\Form\Security\LoginType;
use AppBundle\Form\Security\ResetPasswordType;
use AppBundle\Service\EmailService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends Controller
{

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * SecurityController constructor.
     * @param AuthenticationUtils $authenticationUtils
     * @param ValidatorInterface $validator
     */
    public function __construct(AuthenticationUtils $authenticationUtils, ValidatorInterface $validator)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->validator = $validator;
    }

    public function loginAction()
    {
        $user = new User();
        $user->setEmail($this->authenticationUtils->getLastUsername());

        $error = $this->authenticationUtils->getLastAuthenticationError();

        $form = $this->createForm(LoginType::class, $user, [
            'action' => $this->generateUrl('default')
        ]);

        return $this->render('@App/base/sidebar/security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/forget_password", name="security_forget_password")
     *
     * @throws \Exception
     */
    public function forgetPasswordAction(Request $request, EmailService $emailService)
    {
        $user = new User();
        $user->setEmail($this->authenticationUtils->getLastUsername());

        $form = $this->createForm(ForgetPasswordType::class, $user);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $this->render('@App/security/forget_password.html.twig', [
                'form' => $form->createView()
            ]);
        }

        /** @var User $user */
        $user = $this->getDoctrine()
            ->getManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => $user->getEmail()]);
        if (!$user) {
            return $this->render('@App/security/forget_password.html.twig', [
                'form' => $form->createView(),
                'message' => 'User not found'
            ]);
        }

        $user->setHash(Uuid::uuid4());
        $this->getDoctrine()->getManager()->flush();

        $message = (new \Swift_Message('Reset password'))
            ->setFrom('support@symfoblog.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('@App/emails/forget_password.html.twig', ['user' => $user]),
                'text/html'
            );
        $emailService->sendEmail($message);

        $form = $this->createForm(ForgetPasswordType::class, new User());

        return $this->render('@App/security/forget_password.html.twig', [
            'form' => $form->createView(),
            'message' => 'Email with link to reset password has been successfully sent to your email!'
        ]);
    }

    /**
     * @Route("/reset_password/{hash}", name="security_reset_password")
     *
     * @throws \Exception
     */
    public function resetPasswordAction(Request $request, string $hash)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['hash' => $hash]);

        if (!$user)
            throw new \Exception("Page not found");

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && !count($this->validator->validate($form, null, ['reset_password']))) {
            $user->setHash(null);
            $em->flush();

            return $this->redirectToRoute('default');
        }

        return $this->render('@App/security/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
