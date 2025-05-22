<?php

namespace App\Core\Services;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Twig\TwigFunction;

class EmailService
{

  public static function send(User $user, string $subject, string $message, int $render = 0, string $lang = 'fr', $userTheme = 'dar')
  {
    $identifiant_user = ['email' => "hindra@98.com", 'name' => 'Hindra98'];


    // Configurer Twig
    $loader = new FilesystemLoader(__DIR__ . '/../../templates');
    $twig = new Environment($loader, ['charset' => 'UTF-8']);
    $twig->addExtension(new IntlExtension());

    // Configurer les traductions
    $translator = new Translator($lang); // 'fr' pour le français, 'en' pour l'anglais
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', __DIR__ . '/../../translations/messages.fr.yaml', 'fr');
    $translator->addResource('yaml', __DIR__ . '/../../translations/messages.en.yaml', 'en');
    $twig->addExtension(new TranslationExtension($translator));

    // Ajouter la fonction `trans` à Twig
    $twig->addFunction(new TwigFunction('trans', function ($id, array $parameters = [], $domain = null, $locale = null) use ($translator) {
      return $translator->trans($id, $parameters, $domain, $locale);
    }));

    $base_url = 'http://localhost:5173/';
    $reset_password_link = $base_url."oauth/reset-password?$message";
    $verify_email_link = $base_url."oauth/verify-registration?$message";

    $render_template = ['welcome', 'reset_password', 'send_otp', 'verify_email', 'update_email'];

    // Rendre le template
    $template = $twig->render('emails/' . $render_template[$render] . '.html.twig', [
      'lang' => $lang, // ou 'en' pour l'anglais
      'subject' => $subject,
      'theme' => $userTheme,
      'reset_password_link' => $reset_password_link,
      'verify_email_link' => $verify_email_link,
      'user' => $user,
      'otp' => $message,
      'date' => date('Y'),
      'year' => date('Y'),
    ]);

    // Configurer PHPMailer
    $mail = new PHPMailer(true);

    try {
      $mail->CharSet = 'UTF-8';
      $mail->Encoding = 'base64';
      $mail->isSMTP();
      $mail->Host = 'localhost';
      $mail->SMTPAuth = false;
      // $mail->Username = 'ton_email@example.com';
      // $mail->Password = 'ton_mot_de_passe';
      $mail->SMTPSecure = false;
      $mail->Port = 1025;

      $mail->setFrom($identifiant_user['email'], $identifiant_user['name']);
      $mail->addAddress($user->email, $user->email);

      $mail->isHTML(true);
      $mail->encodeHeader('utf8');
      $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
      $mail->Subject = $subject;
      $mail->Body = $template;
      $mail->AltBody = strip_tags($template);

      $mail->send();
      $render_message = ['', 'Un lien de reinitialisation vous a été envoyé par mail !', 'Verifiez vos mails !', 'Un lien de validation vous a été envoyé par mail !', 'Adresse email modifie avec succes'];
      return ['message_email' => $render_message[$render]];
    } catch (Exception $e) {
      return ['message_email' => "Le code n'a pas pu être envoyé. Renvoyez le mail \n Erreur : {$mail->ErrorInfo}."];
    }
  }
}
