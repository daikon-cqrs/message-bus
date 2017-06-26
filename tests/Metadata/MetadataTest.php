<?php

namespace Daikon\Tests\MessageBus\Metadata;

use Daikon\MessageBus\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class MetadataTest extends TestCase
{
    public function testFromArray()
    {
        $metadata = Metadata::fromArray([
            "some_string" => "foo",
            "some_yay" => true,
            "some_number" => 23,
            "some_float" => 23.42,
            "some_array" => [ "captain" => "arr" ]
        ]);
        $this->assertEquals($metadata->get("some_string"), "foo");
        $this->assertTrue($metadata->get("some_yay"));
        $this->assertEquals($metadata->get("some_number"), 23);
        $this->assertEquals($metadata->get("some_float"), 23.42);
        $this->assertEquals($metadata->get("some_array"), [ "captain" => "arr" ]);
    }

    public function testToArray()
    {
        $metadataArray = [
            "foo" => "bar",
            "yay_or_nay" => true,
            "some_number" => 23,
            "some_float" => 23.42,
            "some_array" => [ "captain" => "arr" ]
        ];
        $metadata = Metadata::fromArray($metadataArray);
        $this->assertEquals($metadata->toArray(), $metadataArray);
    }

    public function testWith()
    {
        $emptyMetadata = Metadata::makeEmpty();
        $metadata = $emptyMetadata->with("foo", "bar");
        $this->assertNull($emptyMetadata->get("foo"));
        $this->assertEquals($metadata->get("foo"), "bar");
        $this->assertFalse($metadata->equals($emptyMetadata));
    }

    public function testMagicGet()
    {
        $metadata = Metadata::makeEmpty()->with("foo", [ "bar" => "foobar" ]);
        $this->assertEquals($metadata->foo, [ "bar" => "foobar" ]);
    }

    public function testEquals()
    {
        $metadata = Metadata::makeEmpty()->with("foo", "bar");
        $this->assertTrue($metadata->equals(Metadata::fromArray([ "foo" => "bar" ])));
        $this->assertFalse($metadata->equals(Metadata::fromArray([ "foo" => "baz" ])));
    }
}
