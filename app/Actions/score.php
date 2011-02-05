<?php

class score implements Action {

	public static function ok(Client $client) {
		return true;
	} // function ok

	public static function run(Client $client, $args) {
		$client->message(<<<FOO
{R                                          ________________{0
{R ________________________________________/{xlord{xfirehed{x
{R| {xYou aregod of war{x
{R| {xYou are level {B110{x, and are {B1157{x years old.
{R| {xYou are a {bMale {Welf{x {Gwarrior{x
{R| {xYou have {B15{x practices and {B11{x training sessions.
{R| {xCarrying {G5{x/{B12{x items at {G15{x/{B8{x pounds.
{R| {r999/{R1000HPs{x, {b888/{B889Mana{x, {g100/{G100Moves{0.
{R ----------------------------------------------------------{x
{R|    {wStr: {R25{M({r25{M){x         {R|      {y-{Y={WSTATS{Y={y-{x
{R|    {wInt: {R26{M({r26{M){x         {R|    {cDefense:{C     4{x
{R|    {wWis: {R27{M({r27{M){x         {R|    {cSaves:{C       6{x{x
{R|    {wDex: {R30{M({r30{M){x         {R|    {cHitroll:{C  1000{x
{R|    {wCon: {R80{M({r80{M){x         {R|    {cDamroll:{C  1000{x
{R ----------------------------------------------------------{x
{R| {xYou have {W2131414{x platinum, {Y123{x gold and {w823{x silver coins.
{R| {xYou have scored {C3287348 exp{x. You need {C1 exp{x to level.
{R| {xYou are {Wangelic{x.   {xAlignment: {B3429{x
{R| {xYou have {M99775{x quest points.
{R| {xYou can quest again in less than one minute!{x
{R| {WHoly Light: {x{YOn{x{W  Wizi {Y100{x{W  Incog {Y99{x
{R| {BYou have been invited to join clan {x[{BKOTR{x]
{R| {0You have {W0{0 ticks in the corner
{R ----------------------------------------------------------{x
FOO
);
	} // function run

} // class score
