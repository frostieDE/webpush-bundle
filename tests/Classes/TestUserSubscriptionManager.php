<?php

namespace BenTools\WebPushBundle\Tests\Classes;

use BenTools\WebPushBundle\Model\Subscription\UserSubscriptionInterface;
use BenTools\WebPushBundle\Model\Subscription\UserSubscriptionManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

final class TestUserSubscriptionManager implements UserSubscriptionManagerInterface
{
    private $subscriptions;

    public function __construct() {
        $this->subscriptions = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function factory(UserInterface $user, string $subscriptionHash, array $subscription, array $options = []): UserSubscriptionInterface
    {
        return new TestUserSubscription(
            $user,
            $subscription['endpoint'],
            $subscription['keys']['p256dh'],
            $subscription['keys']['auth'],
            $subscriptionHash
        );
    }

    /**
     * @inheritDoc
     */
    public function hash(string $endpoint, UserInterface $user): string
    {
        return md5($endpoint);
    }

    /**
     * @inheritDoc
     */
    public function getUserSubscription(UserInterface $user, string $subscriptionHash): ?UserSubscriptionInterface
    {
        return $this->subscriptions->filter(function(UserSubscriptionInterface $subscription) use ($user, $subscriptionHash) {
            return $subscription->getUser() === $user && $subscription->getSubscriptionHash() === $subscriptionHash;
        })->first() ?: null;
    }

    /**
     * @inheritDoc
     */
    public function findByUser(UserInterface $user): iterable
    {
        return $this->subscriptions->filter(function(UserSubscriptionInterface $subscription) use ($user) {
            return $subscription->getUser() === $user;
        });
    }

    /**
     * @inheritDoc
     */
    public function findByHash(string $subscriptionHash): iterable
    {
        return $this->subscriptions->filter(function(UserSubscriptionInterface $subscription) use ($subscriptionHash) {
            return $subscription->getSubscriptionHash() === $subscriptionHash;
        });
    }

    /**
     * @inheritDoc
     */
    public function save(UserSubscriptionInterface $userSubscription): void
    {
        $this->subscriptions->add($userSubscription);
    }

    /**
     * @inheritDoc
     */
    public function delete(UserSubscriptionInterface $userSubscription): void
    {
        $this->subscriptions->removeElement($userSubscription);
    }
}
