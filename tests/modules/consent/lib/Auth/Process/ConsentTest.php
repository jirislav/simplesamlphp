<?php
/**
 * Test for the consent:Process filter.
 *
 * @author Vincent Rioux <vrioux@ctech.ca>
 * @package SimpleSAMLphp
 */

// Consent module has no namespace yet.  We should add it and then add it here also
//namespace SimpleSAML\Test\Module\consent\Auth\Process;


class ConsentTest extends \PHPUnit_Framework_TestCase
{

    /*
     * Helper function to run the filter with a given configuration.
     *
     * @param array $config  The filter configuration.
     * @param array $request  The request state.
     * @return array  The state array after processing.
     */
    private function processFilter(array $config, array $request)
    {
        $filter = new sspmod_consent_Auth_Process_Consent($config, null);
        $filter->process($request);
        return $request;
    }


    /**
     * Test valid consent disable.
     */
    public function testValidConsentDisable()
    {
        // test consent disable regex with match
        $config = array();

        // test consent disable with match on specific SP entityid
        $request = array(
            'Source'     => array(
                'entityid' => 'https://idp.example.org',
                'metadata-set' => 'saml20-idp-local',
                'consent.disable' => array(
                    'https://valid.flatstring.example.that.does.not.match',
                    array(), // invalid consent option array should be ignored
                    array('type'=>'invalid'), // invalid consent option type should be ignored
                    array('type'=>'regex'), // regex consent option without pattern should be ignored
                    array('type'=>'regex', 'pattern'=>'/.*\.valid.regex\.that\.does\.not\.match.*/i'),
                    'https://sp.example.org/my-sp', // accept the SP that has this specific entityid
                ),
                'SingleSignOnService' => array(
                    array(
                        'Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                        'Location' => 'https://idp.example.org/saml2/idp/SSOService.php',
                    ),
                ),
            ),
            'Destination' => array(
                'entityid' => 'https://sp.example.org/my-sp', // valid entityid equal to the last one in the consent.disable array
                'metadata-set' => 'saml20-sp-remote',
            ),
            'UserID' => 'jdoe',
            'Attributes' => array(
                'eduPersonPrincipalName' => array('jdoe@example.com'),
            ),
        );
        $result = $this->processFilter($config, $request);
        $this->assertEquals($request, $result); // The state should NOT have changed because NO consent should be necessary (match)

        // test consent disable with match on SP through regular expression
        $request = array(
            'Source'     => array(
                'entityid' => 'https://idp.example.org',
                'metadata-set' => 'saml20-idp-local',
                'consent.disable' => array(
                    array('type'=>'regex', 'pattern'=>'/.*\.example\.org.*/i'), // accept any SP that has an entityid that contains the string ".example.org"
                ),
                'SingleSignOnService' => array(
                    array(
                        'Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                        'Location' => 'https://idp.example.org/saml2/idp/SSOService.php',
                    ),
                ),
            ),
            'Destination' => array(
                'entityid' => 'https://sp.example.org/my-sp', // sp contains the string ".example.org"
                'metadata-set' => 'saml20-sp-remote',
            ),
            'UserID' => 'jdoe',
            'Attributes' => array(
                'eduPersonPrincipalName' => array('jdoe@example.com'),
            ),
        );
        $result = $this->processFilter($config, $request);
        $this->assertEquals($request, $result); // The state should NOT have changed because NO consent should be necessary (match)

    }

}
