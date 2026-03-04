<?php

namespace App\Enums;

enum MembershipType: string
{
    case FullFamily    = 'full_family';
    case Single        = 'single';
    case Associate     = 'associate';
    case FirstYearFree = 'first_year_free';
    case Donor         = 'donor';

    public function label(): string
    {
        return match($this) {
            self::FullFamily    => 'Full Family',
            self::Single        => 'Single Member',
            self::Associate     => 'Associate',
            self::FirstYearFree => 'Complimentary',
            self::Donor         => 'Donor',
        };
    }

    public static function fromQbCustomerType(string $typeName): self
    {
        $n = strtolower(trim($typeName));

        if ($n === '') {
            return self::Donor;
        }
        // Complimentary beats everything
        if (str_contains($n, 'complimentary')) {
            return self::FirstYearFree;
        }
        // Associate beats member family
        if (str_contains($n, 'associate')) {
            return self::Associate;
        }
        // Member Family
        if (str_contains($n, 'member family')) {
            return self::FullFamily;
        }
        // Single Member
        if (str_contains($n, 'single member')) {
            return self::Single;
        }

        return self::Donor;
    }

    /** @deprecated use fromQbCustomerType */
    public static function fromQbDonorType(string $donorType): self
    {
        return self::fromQbCustomerType($donorType);
    }
}
