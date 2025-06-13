<?php

namespace MiniFAIR\Keys;

use Exception;
use Elliptic\EC;
use Elliptic\EC\KeyPair;
use YOCLIB\Multiformats\Multibase\Multibase;

const CURVE_K256 = 'secp256k1';
const CURVE_P256 = 'p256';
const CURVE_ED25519 = 'ed25519';

// From https://github.com/multiformats/multicodec/blob/master/table.csv:
// 0xe7 0x01 = varint( 0xe7 ) = 231 = secp256k1-pub
// 0x80 0x24 = varint( 0x80 ) = 128 = p256-pub
// 0xed 0x01 = varint( 0xed ) = 237 = ed25519-pub
const PREFIX_CURVE_K256 = "\xe7\x01";
const PREFIX_CURVE_P256 = "\x80\x24";
const PREFIX_CURVE_ED25519 = "\xed\x01";

/**
 * Convert a multibase public key string to a keypair object.
 *
 * @see https://atproto.com/specs/cryptography
 *
 * @throws Exception If the curve is not supported.
 * @param string $key The multibase public key string (starts with z).
 * @return KeyPair The keypair object.
 */
function decode_public_key( string $key ) : KeyPair {
	static $cache = [];
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$decoded = Multibase::decode( $key );

	$curve = match ( $decoded[0] ) {
		"\x80" => CURVE_P256,
		"\xE7" => CURVE_K256,
		default => throw new Exception( 'Unsupported curve' ),
	};

	$ec = new EC( $curve );

	$stripped = bin2hex( substr( $decoded, 2 ) );
	$keypair = $ec->keyFromPublic( $stripped, 'hex' );
	$cache[ $key ] = $keypair;
	return $keypair;
}

/**
 * Convert a multibase private key string to a keypair object.
 *
 * @see https://atproto.com/specs/cryptography
 *
 * @throws Exception If the curve is not supported.
 * @param string $key The multibase public key string (starts with z).
 * @return KeyPair The keypair object.
 */
function decode_private_key( string $key ) : KeyPair {
	static $cache = [];
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$decoded = Multibase::decode( $key );

	$curve = match ( $decoded[0] ) {
		"\x80" => CURVE_P256,
		"\xE7" => CURVE_K256,
		default => throw new Exception( 'Unsupported curve' ),
	};

	$ec = new EC( $curve );

	$stripped = bin2hex( substr( $decoded, 2 ) );
	$keypair = $ec->keyFromPrivate( $stripped, 'hex' );
	$cache[ $key ] = $keypair;
	return $keypair;
}

/**
 * Decode a did:key: string to a keypair object.
 *
 * @throws Exception If the did:key: string is invalid.
 * @param string $did The did:key: string.
 * @return KeyPair The keypair object.
 */
function decode_did_key( string $did ) : KeyPair {
	if ( ! str_starts_with( $did, 'did:key:' ) ) {
		throw new Exception( 'Invalid DID format' );
	}
	$did = substr( $did, 8 );
	if ( ! str_starts_with( $did, 'z' ) ) {
		throw new Exception( 'Invalid DID format' );
	}

	return decode_public_key( $did );
}

/**
 * Convert a keypair object to a multibase public key string.
 *
 * @see https://atproto.com/specs/cryptography
 *
 * @throws Exception If the curve is not supported.
 * @param KeyPair $key The keypair object.
 * @param string $curve The curve to use (CURVE_K256 or CURVE_P256).
 * @return string The multibase public key string (starts with z).
 */
function encode_public_key( KeyPair $key, string $curve ) : string {
	$pub = $key->getPublic( true, 'hex' );
	$prefix = match ( $curve ) {
		CURVE_K256 => 'e701',
		CURVE_P256 => '8024',
		default => throw new Exception( 'Unsupported curve' ),
	};

	$encoded = Multibase::encode( Multibase::BASE58BTC, hex2bin( $prefix . $pub ) );
	return $encoded;
}

/**
 * Convert a keypair object to a multibase private key string.
 *
 * @see https://atproto.com/specs/cryptography
 *
 * @throws Exception If the curve is not supported.
 * @param KeyPair $key The keypair object.
 * @param string $curve The curve to use (CURVE_K256 or CURVE_P256).
 * @return string The multibase private key string (starts with z).
 */
function encode_private_key( KeyPair $key, string $curve ) : string {
	$priv = $key->getPrivate( 'hex' );
	$prefix = match ( $curve ) {
		CURVE_K256 => 'E701',
		CURVE_P256 => '8024',
		default => throw new Exception( 'Unsupported curve' ),
	};

	$encoded = Multibase::encode( Multibase::BASE58BTC, hex2bin( $prefix . $priv ));
	return $encoded;
}

/**
 * Convert a keypair object to a did:key: string.
 *
 * @throws Exception If the curve is not supported.
 * @param KeyPair $key The keypair object.
 * @param string $curve The curve to use (CURVE_K256 or CURVE_P256).
 * @return string The did:key: string.
 */
function encode_did_key( KeyPair $key, string $curve ) : string {
	$encoded = encode_public_key( $key, $curve );
	return 'did:key:' . $encoded;
}
