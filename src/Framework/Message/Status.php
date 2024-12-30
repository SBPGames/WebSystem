<?php

namespace SBPGames\Framework\Message;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
enum Status: int{
	// New status codes should only be added when understood and needed.

	// Informational
	// -

	// Success
	case OK = 200;
	case CREATED = 201;
	case NO_CONTENT = 204;

	// Redirection
	case MOVED_PERMANENTLY = 301; // Similar to 308: allows moving to GET.
	case FOUND = 302; // Similar to 307: allows moving to GET.
	case TEMPORARY_REDIRECT = 307;
	case PERMANENT_REDIRECT = 308;

	// Client Error
	case BAD_REQUEST = 400;
	case UNAUTHORIZED = 401;
	case FORBIDDEN = 403;
	case NOT_FOUND = 404;
	case METHOD_NOT_ALLOWED = 405;
	case CONFLICT = 409; // Conflict with server state.
	case UNSUPPORTED_MEDIA_TYPE = 415; // Missing, invalid or unsupported type.
	case IM_A_TEAPOT = 418; // Server can't brew coffee because it's a teapot.
	case MISDIRECTED_REQUEST = 421; // Wrong server, scheme or authority.

	// Server Error
	case INTERNAL_ERROR = 500;
	case NOT_IMPLEMENTED = 501;
	case SERVICE_UNAVAILABLE = 503;

	// GETTERS
	public function getReasonPhrase(): string{
		return match($this->value){
			// Informational
			// -

			// Success
			200 => "OK",
			201 => "Created",
			204 => "No Content",

			// Redirection
			301 => "Moved Permanently",
			302 => "Found",
			307 => "Temporary Redirect",
			308 => "Permanent Redirect",

			// Client Error
			400 => "Bad Request",
			401 => "Unauthorized",
			403 => "Forbidden",
			404 => "Not Found",
			405 => "Method Not Allowed",
			409 => "Conflict",
			415 => "Unsupported Media Type",
			418 => "I'm a teapot",
			421 => "Misdirected Request",

			// Server Error
			500 => "Internal Server Error",
			501 => "Not Implemented",
			503 => "Service Unavailable",

			default => "#INVALID"
		};
	}
}