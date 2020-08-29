<?php

namespace PHPSTORM_META {

    use App\Models\Game;
    use phpDocumentor\Reflection\Types\Integer;
    use Psr\Container\ContainerInterface;

    override(ContainerInterface::get(),
        map([
            'game.instance' => Game::class,
        ])
    );
}
