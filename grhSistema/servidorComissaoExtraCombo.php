<?php

# Configuração
include ("_config.php");

# Conecta ao banco de dados
$pessoal = new Pessoal();

$tipo = $_GET['tipo'];

# Monta o select
$descricao = $pessoal->select('SELECT idDescricaoComissao,
                                      tbdescricaocomissao.descricao
                                 FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                                WHERE tbdescricaocomissao.idTipoComissao = ' . $tipo . ' 
                             ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao,  tbdescricaocomissao.descricao');

echo '<label id="labelidTipoComissao" for="idTipoComissao">Tipo da Cargo em Comissão: * </label>';

foreach ($descricao as $dd) {
    echo "<option value=$dd[0]>$dd[1]</option>n";
}

echo "</select>n";
