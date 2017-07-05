
<div class="container">
	<table class="table">
		<thead>
			<tr>
				<th>id</th>
				<th>age</th>
				<th>money</th>
				<th>level</th>
				<th>created_at</th>
			</tr>
		</thead>
		<tbody>
		  <?php foreach($dataProvider as $v) :?>
			<tr>
				<td><?php echo $v['id'];?></td>
				<td><?php echo $v['age'];?></td>
				<td><?php echo $v['money'];?></td>
				<td><?php echo $v['level'];?></td>
				<td><?php echo $v['created_at'];?></td>
			</tr>
			<?php  endforeach;?>
		</tbody>
	</table>
</div>