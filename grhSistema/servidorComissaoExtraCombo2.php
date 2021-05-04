<?php

# Configuração
include ("_config.php");

# Conecta ao banco de dados
$pessoal = new Pessoal();

$tipo = $_GET['tipo'];
$descricao = $_GET['descricao'];

# Monta o select
# Primeiro select pega os cargos com a mesma descrição do $id
$selectOcupante1 = 'SELECT idComissao,
                                  CONCAT(DATE_FORMAT(dtNom,"%d/%m/%Y")," - ",DATE_FORMAT(dtExo,"%d/%m/%Y")," | ",tbpessoa.nome," | ",tbperfil.nome) as ff,
                                  tbdescricaocomissao.descricao
                             FROM tbcomissao as tb1 JOIN tbservidor USING (idServidor) 
                                                    JOIN tbpessoa USING (idPessoa)
                                                    JOIN tbperfil USING (idPerfil)
                                                    JOIN tbdescricaocomissao USING (idDescricaoComissao)';
# Seleciona somente os de mesmo cargo
$selectOcupante1 .= ' WHERE tbdescricaocomissao.idTipoComissao = ' . $tipo;

# Seleciona somente os exinerados
$selectOcupante1 .= ' AND tbdescricaocomissao.idDescricaoComissao = ' . $descricao;

# Seleciona somente os exinerados
$selectOcupante1 .= ' AND dtExo IS NOT null';

# Impede que o mandato já escolhido apareça
$selectOcupante1 .= ' AND tb1.idComissao NOT IN (SELECT idAnterior FROM tbcomissao as tb2 WHERE idAnterior IS NOT null)';

# Ordena pela descrição e data de nomeação para facilitar o agrupamento
$selectOcupante1 .= ' ORDER BY tbdescricaocomissao.descricao, dtNom desc';
$ocupanteAnterior1 = $pessoal->select($selectOcupante1);

# Segundo select pega os cargos que sao diferentes do $id, pois existe 
# remota possibilidade do cargp anterior ser de outra descrição
$selectOcupante2 = 'SELECT idComissao,
                                  CONCAT(DATE_FORMAT(dtNom,"%d/%m/%Y")," - ",DATE_FORMAT(dtExo,"%d/%m/%Y")," | ",tbpessoa.nome," | ",tbperfil.nome) as ff,
                                  tbdescricaocomissao.descricao
                             FROM tbcomissao as tb1 JOIN tbservidor USING (idServidor) 
                                                    JOIN tbpessoa USING (idPessoa)
                                                    JOIN tbperfil USING (idPerfil)
                                                    JOIN tbdescricaocomissao USING (idDescricaoComissao)';
# Seleciona somente os de mesmo cargo
$selectOcupante2 .= ' WHERE tbdescricaocomissao.idTipoComissao = ' . $tipo;

# Seleciona somente os exinerados
$selectOcupante2 .= ' AND tbdescricaocomissao.idDescricaoComissao <> ' . $descricao;

# Seleciona somente os exinerados
$selectOcupante2 .= ' AND dtExo IS NOT null';

# Impede que o mandato já escolhido apareça
$selectOcupante2 .= ' AND tb1.idComissao NOT IN (SELECT idAnterior FROM tbcomissao as tb2 WHERE idAnterior IS NOT null)';

# Ordena pela descrição e data de nomeação para facilitar o agrupamento
$selectOcupante2 .= ' ORDER BY tbdescricaocomissao.descricao, dtNom desc';
$ocupanteAnterior2 = $pessoal->select($selectOcupante2);

# Junta os arrays
$ocupanteAnterior = array_merge($ocupanteAnterior1, $ocupanteAnterior2);

array_unshift($ocupanteAnterior, [null, null]);

echo '<label id="labelidAnterior" for="idAnterior">Ocupante Anterior:</label>';

$optgroupAnterior = null;

foreach ($ocupanteAnterior as $dd) {
    # Verifica se mudou o grupo
    if ($optgroupAnterior <> $dd[2]) {

        # Varifica se não é o prmeiro grupo                            
        if (is_null($optgroupAnterior)) {
            echo '</optgroup>';
        }

        echo '<optgroup label="' . $dd[2] . '">';

        $optgroupAnterior = $dd[2];
    }

    echo "<option value=$dd[0]>$dd[1]</option>n";
}

echo "</select>n";
