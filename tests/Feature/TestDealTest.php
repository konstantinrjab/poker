<?php

namespace Tests\Feature;

use Tests\TestCase;

class TestDealTest extends TestCase
{
    public function testDeal()
    {
        $users = [
            [
                'id' => 'pl__1',
                'name' => 'player 1',
                'cards' => [
                    [
                        'suit' => 'Club',
                        'value' => '2'
                    ],
                    [
                        'suit' => 'Heart',
                        'value' => '5'
                    ]
                ]
            ],
            [
                'id' => 'pl__2',
                'name' => 'player 2',
                'cards' => [
                    [
                        'suit' => 'Spade',
                        'value' => '12'
                    ],
                    [
                        'suit' => 'Diamond',
                        'value' => '13'
                    ]
                ]
            ],
            [
                'id' => 'pl__3',
                'name' => 'player 3',
                'cards' => [
                    [
                        'suit' => 'Club',
                        'value' => '11'
                    ],
                    [
                        'suit' => 'Club',
                        'value' => '10'
                    ]
                ]
            ],
        ];
        $response = $this->post('/api/test/', [
            'tableCards' => [
                [
                    'suit' => 'Club',
                    'value' => '7'
                ],
                [
                    'suit' => 'Diamond',
                    'value' => '9'
                ],
                [
                    'suit' => 'Heart',
                    'value' => '4'
                ],
                [
                    'suit' => 'Heart',
                    'value' => '6'
                ],
                [
                    'suit' => 'Club',
                    'value' => '8'
                ],
            ],
            'users' => $users
        ])->json();
    }
}
