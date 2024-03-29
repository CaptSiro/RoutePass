<?php

namespace RoutePass\tree\path\parser;

enum TokenType: string {

    case IDENT = "ident";

    case BRACKET_L = "bracket_l";

    case BRACKET_R = "bracket_r";

    case SLASH = "slash";

    case EOF = "eof";

    case ILLEGAL = "illegal";
}
