<?php


use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebsocketBundle extends Bundle
{
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new WebsocketExtension();
        }

        return $this->extension;
    }
}
