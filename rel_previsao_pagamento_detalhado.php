<?php  
  require  "common.inc.php"; 
  verifica_seguranca($_SESSION[PAP_RESP_PEDIDO] || $_SESSION[PAP_RESP_NUCLEO] || $_SESSION[PAP_ACOMPANHA_PRODUTOR] || $_SESSION[PAP_ACOMPANHA_RELATORIOS]);
  top();
  
 $cha_id=request_get("cha_id","");
                      
 $sql = "SELECT prodt_nome, prodt_mutirao, DATE_FORMAT(cha_dt_entrega,'%d/%m/%Y') cha_dt_entrega ";
 $sql.= "FROM chamadas LEFT JOIN produtotipos ON prodt_id = cha_prodt ";
 $sql.= "WHERE cha_id = " . prep_para_bd($cha_id);
 $res = executa_sql($sql);
 $row = mysqli_fetch_array($res,MYSQLI_ASSOC);

 if(!$res)
 {
	 redireciona(PAGINAPRINCIPAL);
 }

?>

<legend>Relatório - Previsão de Pagamento Detalhado - <?php echo($row["prodt_nome"]); ?> - Entrega em <?php echo($row["cha_dt_entrega"]); ?></legend>
<br>

<?php 


$sql="SELECT  forn_nome_curto, prod_nome, prod_valor_compra,prod_unidade, chaprod_recebido, ";
$sql.="FORMAT(sum(pedprod_quantidade),1) total_demada, FORMAT(GREATEST(0,sum(pedprod_quantidade) - ifnull(est_prod_qtde_depois,0)),1) total_demada_menos_estoque ";
$sql.="FROM chamadaprodutos ";
$sql.="LEFT JOIN chamadas on cha_id = chaprod_cha ";
$sql.="LEFT JOIN produtos on prod_id = chaprod_prod ";
$sql.="LEFT JOIN pedidos ON ped_cha = cha_id ";
$sql.="LEFT JOIN nucleos ON ped_nuc = nuc_id ";
$sql.="LEFT JOIN pedidoprodutos ON pedprod_ped = ped_id AND pedprod_prod=chaprod_prod ";
$sql.="LEFT JOIN fornecedores on prod_forn = forn_id ";
$sql.="LEFT JOIN estoque ON est_prod = chaprod_prod AND est_cha = " . prep_para_bd(get_chamada_anterior($cha_id)) . " ";
$sql.="WHERE ped_cha= " . prep_para_bd($cha_id) . " ";
$sql.="AND ped_fechado = '1' ";
$sql.="AND chaprod_disponibilidade <> '0' ";
$sql.="AND prod_ini_validade<=cha_dt_entrega AND prod_fim_validade>=cha_dt_entrega  ";
$sql.="GROUP BY  forn_id,prod_id ";
$sql.="ORDER BY forn_nome_curto,prod_nome, prod_unidade ";


$res = executa_sql($sql);

		if($res)
		{
		   $ultimo_forn = "";
		   $total_valor_fornecedor=0;
		   $total_geral_rede=0;	
		   
		   ?>
		   <input class="btn btn-success" type="button" value="selecionar tabela para copiar" 
           onclick="selectElementContents( document.getElementById('selectable') );">
           <br><br>
           
  		   <div id="selectable">
           
		   <?php		   
		   
		   while ($row = mysqli_fetch_array($res,MYSQLI_ASSOC)) 
		   {
				if($row["forn_nome_curto"]!=$ultimo_forn)
				{	
				
					if($ultimo_forn!="")
					{
						?>
                           <tr>
				         	<th colspan="4" style="text-align:right">TOTAL a pagar: </th>                             
				            <th colspan="2"  style="text-align:center">R$ <?php echo(formata_moeda($total_valor_fornecedor));?></th>
				           </tr>
                          	</tbody>
							</table>		
						<?php
					}
					
					$total_valor_fornecedor=0;
					$ultimo_forn = $row["forn_nome_curto"];
					
								
					?>
                    <table class="table table-striped table-bordered table-condensed">
                    <thead>
                        <tr>
                                  <th><?php echo($row["forn_nome_curto"]);?></th>
                                  <th>Unidade</th>
                                  <th>Valor (R$)</th>    
                                  <th>Total Pedido</th>     
                                  <th>Total Entregue</th>
                                  <th>A pagar (R$)</th>                                            
                        </tr>
                     </thead>           		   
                    <tbody>
			 
					<?php
                    		
				}  
				
				?>
				<tr> 
				<td><?php echo($row["prod_nome"]);?></td>
				<td><?php echo($row["prod_unidade"]);?></td>
				<td><?php echo(formata_moeda($row["prod_valor_compra"]));?></td>
   
                <td><?php echo(formata_numero_de_mysql($row["total_demada_menos_estoque"]));?></td>
                <td><?php echo(formata_numero_de_mysql($row["chaprod_recebido"]));?></td>
                    
				<td><?php echo (formata_moeda($row["chaprod_recebido"] * $row["prod_valor_compra"])); ?></td>
			
				</tr>
				 
				<?php
				$total_valor_fornecedor+=$row["chaprod_recebido"] * $row["prod_valor_compra"];
				$total_geral_rede+=$row["chaprod_recebido"] * $row["prod_valor_compra"];


		   }
		   
		  ?>  
          
              <tr>
                    <th colspan="4" style="text-align:right">TOTAL a pagar: </th>                               
                    <th colspan="2"  style="text-align:center">R$ <?php echo(formata_moeda($total_valor_fornecedor));?></th>
                   </tr>
                   
                 
                   
         
          </tbody>
		   </table>
           
           
           
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                          <th colspan="2"  style="text-align:center">Somatório Geral</th>
                          
                      </tr>                   
                 </thead>	   
                <tbody>                               
                      <tr>
                    	<th style="text-align:right">TOTAL a pagar: </th>               
                        <th style="text-align:center">R$ <?php echo(formata_moeda($total_geral_rede));?></th>
                     </tbody>
		   </table>
           
           
           </div>
           
		   <?php
		   
		   
		} 
 

 
	footer();
?>