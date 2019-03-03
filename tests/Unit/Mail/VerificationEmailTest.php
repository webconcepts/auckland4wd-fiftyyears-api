<?php

use App\User;
use App\Mail\VerificationEmail;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class VerificationEmailTest extends TestCase
{
    use DatabaseMigrations;

    protected function renderTextView($mailable)
    {
        $mailable->build();
        return view($mailable->textView, $mailable->buildViewData())->render();
    }

    /** @test **/
    public function html_email_contains_link_with_the_users_verification_code_and_a_support_email_address()
    {
        $user = factory(User::class)->create([
            'verification_code' => 'TEST_VERIFICATION_CODE'
        ]);

        $email = new VerificationEmail($user);
        $html = $email->render();

        $this->assertContains(
            ' href="https://fiftyyears.auckland4wd.org.nz/verify/TEST_VERIFICATION_CODE"',
            $html
        );
        $this->assertContains('jeremy@auckland4wd.org.nz', $html);
    }

    /** @test **/
    public function plain_text_email_contains_url_with_the_users_verification_code_and_a_support_email_address()
    {
        $user = factory(User::class)->create([
            'verification_code' => 'TEST_VERIFICATION_CODE'
        ]);

        $email = new VerificationEmail($user);
        $plainText = $this->renderTextView($email);

        $this->assertContains(
            'https://fiftyyears.auckland4wd.org.nz/verify/TEST_VERIFICATION_CODE',
            $plainText
        );
        $this->assertContains('jeremy@auckland4wd.org.nz', $plainText);
    }

    /** @test **/
    public function email_subject_contains_keywords()
    {
        $user = factory(User::class)->create([
            'verification_code' => 'TEST_VERIFICATION_CODE'
        ]);

        $email = new VerificationEmail($user);
        $subject = $email->build()->subject;

        $this->assertContains('auckland', $subject, '', true);
        $this->assertContains('club', $subject, '', true);
        $this->assertContains('login', $subject, '', true);
        $this->assertContains('fifty', $subject, '', true);
        $this->assertContains('years', $subject, '', true);
    }
}
