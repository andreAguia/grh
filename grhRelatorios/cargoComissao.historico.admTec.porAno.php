<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega o ano exercicio
    $parametroAno = post("parametroAno", date('Y'));

    ######

    $servidor = new Pessoal();
    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao) comissao,
                     tbcomissao.dtNom,
                     tbcomissao.dtExo
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa) 
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                                     JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                                     JOIN tbtiponomeacao ON (tbcomissao.tipo = tbtiponomeacao.idTipoNomeacao)
              WHERE tbtiponomeacao.visibilidade <> 2
                AND year(tbcomissao.dtNom) <= "' . $parametroAno . '"
                AND (tbcomissao.dtExo IS null OR year(tbcomissao.dtExo) >= "' . $parametroAno . '")                       
                AND tbservidor.situacao = 1
                AND tbtipocargo.tipo = "Adm/Tec"
           ORDER BY 4, tbcomissao.dtNom';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Administrativos e Técnicos Ativos');
    $relatorio->set_tituloLinha2("Com Cargos em Comissão em  {$parametroAno}");
    $relatorio->set_subtitulo('Ordenados pelo Cargo em Comissão');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Comissão', 'Nomeação', 'Exoneração']);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", "date_to_php"]);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_classe([null, null, "Pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoSimples"]);
    $relatorio->set_conteudo($result);

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 2,
            'linha' => 1)
    ));

    $relatorio->show();
    $page->terminaPagina();
}