<?php

namespace PHPSTORM_META {

    use App\Entities\Game;
    use phpDocumentor\Reflection\Types\Integer;
    use Psr\Container\ContainerInterface;

    override(ContainerInterface::get(),
        map([
            'game.instance' => Game::class,
        ])
    );
}
