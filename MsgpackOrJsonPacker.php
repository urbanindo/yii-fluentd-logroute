<?php
namespace Urbanindo\Yii\Component\Logger;

class MsgpackOrJsonPacker implements \Fluent\Logger\PackerInterface
{
    public function __construct()
    {
    }

    /**
     * pack entity with msgpack protocol.
     * {@link https://github.com/msgpack/msgpack-php}
     * @param Entity $entity
     * @return string
     */
    public function pack(Entity $entity)
    {
        if (function_exists('msgpack_pack')) {
            return msgpack_pack(array($entity->getTag(), $entity->getTime(), $entity->getData()));
        } else {
            return json_encode(array($entity->getTag(), $entity->getTime(), $entity->getData())); 
        }
    }
}