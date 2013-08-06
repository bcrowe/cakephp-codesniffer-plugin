<?php

class Foo extends Bar {

	public function test() {
		$x = $y0 . $z0;
		$x = $y1 . $z1;
		$x = $y2 . $z2;
		$x = $y3 . $z3;

		$x = 'y0' . 'z0';
		$x = 'y1' . 'z1';
		$x = 'y2' . 'z2';
		$x = 'y3' . 'z3';

		$x = 'y0';
		$x = 'y1';
		$x = 'y2';

		$x = 'y0';
		$x = 'y1';
		$x = 'y2';

		$this->set('e1','y1');
		$this->set('e2',  'y2');
		$this->set('e3' ,'y3');

		if ($x0 == 1) {
		}
		if ($x1 ==1 ) {
		}
		if ($x2 == 1) {
		}
		if ($x3 == 1) {
		}

		if ($x0 === 1) {
		}
		if ($x1 === 1) {
		}
		if ($x2 === 1) {
		}
		if ($x3 === 1) {
		}
	}

}
