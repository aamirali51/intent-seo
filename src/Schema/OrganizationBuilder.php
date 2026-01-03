<?php

declare(strict_types=1);

namespace Intent\Seo\Schema;

/**
 * Organization schema builder.
 *
 * @example
 * Schema::organization()
 *     ->name('My Company')
 *     ->url('https://example.com')
 *     ->logo('https://example.com/logo.png')
 *     ->build();
 */
class OrganizationBuilder extends BaseBuilder
{
    public function __construct()
    {
        parent::__construct('Organization');
    }

    /**
     * Set the organization name.
     *
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        $this->data['name'] = $name;

        return $this;
    }

    /**
     * Set the description.
     *
     * @param string $description
     * @return self
     */
    public function description(string $description): self
    {
        $this->data['description'] = $description;

        return $this;
    }

    /**
     * Set the URL.
     *
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * Set the logo.
     *
     * @param string $logo Logo URL
     * @return self
     */
    public function logo(string $logo): self
    {
        $this->data['logo'] = [
            '@type' => 'ImageObject',
            'url' => $logo,
        ];

        return $this;
    }

    /**
     * Set social profile URLs.
     *
     * @param array<int, string> $profiles
     * @return self
     */
    public function sameAs(array $profiles): self
    {
        $this->data['sameAs'] = $profiles;

        return $this;
    }

    /**
     * Set contact information.
     *
     * @param string $type Contact type (e.g., 'customer service', 'sales')
     * @param string $phone Phone number
     * @param string|null $email Email address
     * @return self
     */
    public function contactPoint(string $type, string $phone, ?string $email = null): self
    {
        $contact = [
            '@type' => 'ContactPoint',
            'contactType' => $type,
            'telephone' => $phone,
        ];

        if ($email !== null) {
            $contact['email'] = $email;
        }

        $this->data['contactPoint'] = $contact;

        return $this;
    }

    /**
     * Set the founding date.
     *
     * @param string $date YYYY-MM-DD or YYYY format
     * @return self
     */
    public function foundingDate(string $date): self
    {
        $this->data['foundingDate'] = $date;

        return $this;
    }

    /**
     * Set address.
     *
     * @param string $street
     * @param string $city
     * @param string $region
     * @param string $postalCode
     * @param string $country
     * @return self
     */
    public function address(
        string $street,
        string $city,
        string $region,
        string $postalCode,
        string $country
    ): self {
        $this->data['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $street,
            'addressLocality' => $city,
            'addressRegion' => $region,
            'postalCode' => $postalCode,
            'addressCountry' => $country,
        ];

        return $this;
    }
}
