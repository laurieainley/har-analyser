<?php

const UPLOADS_BASE = "uploads";
const THRESHOLD_SLOW = 600;

if(!is_dir(UPLOADS_BASE)) {
	mkdir(UPLOADS_BASE);
	chmod(UPLOADS_BASE, 0777);
}