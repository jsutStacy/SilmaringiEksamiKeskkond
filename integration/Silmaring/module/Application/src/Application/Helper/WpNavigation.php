<?php
namespace Application\Helper;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Client;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WpNavigation extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var $serviceLocator
     */
    protected $serviceLocator;

    /**
     * @var $em
     */
    protected $em;

    /**
     * @var $config
     */
    protected $config;


    /**
     * @param int $type
     * @return string
     */
    public function __invoke($type = 1) {
        $this->config = $this->getServiceLocator()->getServiceLocator()->get('Config');
        $url = $this->generateRequestUrl($type);

        $cache = $this->getServiceLocator()->getServiceLocator()->get('wp_cache');
        $key = 'wp-navigation-'. $type;
        $success = false;
        $navigation = $cache->getItem($key, $success);
        if(empty($navigation)) {
            $navigation =  $this->curlRequest($url);
            $cache->setItem($key, $navigation);
        }
        return $navigation;
    }

    /**
     * @param $url
     * @return string
     * @throws \RuntimeException
     */
    private function curlRequest($url)
    {
        $client = new Client($url);
        $adapter = new Curl();
        $client->setAdapter($adapter);

        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
            )
        ));
        $client->setAdapter($adapter);

        $response = $client->send($client->getRequest());
        $body = $response->getBody();
        if(empty($body)) {
            throw new \RuntimeException("No navigation returned");
        }
        return $body;
    }

    /**
     * Generate full request url
     * @param $type
     * @throws \RuntimeException
     * @return string
     */
    private function generateRequestUrl($type)
    {
        if(empty($this->config['wp_site_token'])) {
            throw new \RuntimeException("No token setup in config");
        }

        return $this->config['wp_site_url'] . '?type=' . $type . '&token=' . $this->config['wp_site_token'];
    }

    /**
     * @return mixed
     */
    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return CustomHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get the service locator.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
}