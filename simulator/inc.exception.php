<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ inc/exception   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

?>
<style type="text/css">
	.plato_exception__ { clear: both; overflow: hidden; margin: 0; padding: 0; text-align: left; width: 900px; border: 1px solid #ccc; background: #fff; }
		.plato_exception__ * { margin: 0; padding: 0; font: 12px Arial, Sans-serif; }
		.plato_exception__ h1 { padding: 20px; font-size: 30px; line-height: 30px; background: #f00; color: #fff; }
		.plato_exception__ h2 { float: left; line-height: 18px; width: 230px; color: #999; }
		.plato_exception__ h3 { float: left; line-height: 18px; }
		.plato_exception__ h4 { margin-top: 15px; font-size: 14px; line-height: 20px; border-bottom: 1px solid #aaa; }
		.plato_exception__ ul { margin: 20px; list-style: none; }
			.plato_exception__ ul li { overflow: hidden; padding: 5px; }
</style>

<div class="plato_exception__">
	<h1>Plato x86 Simulator Exception Reporter</h1>

	<ul>
		<li><h2>ID</h2><h3><?php echo $this->id; ?></h3></li>
		<li><h2>MSG</h2><h3><?php echo $this->message; ?></h3></li>

		<?php if($this->eip !== null) { ?>
			<li><h2>EIP</h2><h3><?php printf('%08X', $this->eip); ?></h3></li>
		<?php } ?>
		<?php if($this->rva !== null) { ?>
			<li><h2>RVA</h2><h3><?php printf('%08X', $this->rva); ?></h3></li>
		<?php } ?>
		<?php if($this->offset !== null) { ?>
			<li><h2>OFFSET</h2><h3><?php printf('%08X', $this->offset); ?></h3></li>
		<?php } ?>
		<?php if(!empty($this->opcode)) { ?>
			<li><h2>CODE</h2><h3><?php echo $this->opcode; ?></h3></li>
		<?php } ?>

		<?php if(!$this->system) { ?>
			<?php if($this->comment) { ?>
				<li><h4>COMMENT</h4></li>

				<?php foreach($this->comment as $key => $value) { ?>
					<li><h2><?php echo $key; ?></h2><h3><?php echo $value; ?></h3></li>
				<?php } ?>
			<?php } ?>

			<?php if($this->stack) { ?>
				<li><h4>STACK</h4></li>
				<li>
					<h2 style="width: 300px;">function</h2>
					<h2 style="width: 80px;">line</h2>
					<h2>file</h2>
				</li>

				<?php foreach($this->stack as $data) { ?>
					<li>
						<h3 style="width: 300px;"><?php echo $data['function']; ?></h3>
						<h3 style="width: 80px;"><?php echo $data['line']; ?></h3>
						<h3><?php echo $data['file']; ?></h3>
					</li>
				<?php } ?>
			<?php } ?>

			<?php if($this->argument) { ?>
				<li><h4>ARGUMENT</h4></li>
				<li>
					<h2 style="width: 100px;">key</h2>
					<h2>value</h2>
				</li>

				<?php foreach($this->argument as $key => $value) { ?>
					<li>
						<h3 style="width: 100px;">#<?php echo $key; ?></h3>
						<h3><?php echo $value; ?></h3>
					</li>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
