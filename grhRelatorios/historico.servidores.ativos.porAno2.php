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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros
    $cargo = "Adm/Tec";
    $parametroAno = get('parametroAno', date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbtipocargo.cargo,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     dtAdmissao,
                     dtDemissao
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE year(dtAdmissao) <= "' . $parametroAno . '"
                 AND (dtDemissao IS null OR year(dtDemissao) >= "' . $parametroAno . '")
                 AND tbtipocargo.tipo = "' . $cargo . '"
                 AND (idPerfil = 1 OR idPerfil = 4)    
            ORDER BY tbtipocargo.nivel, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Histórico de Servidores');
    $relatorio->set_tituloLinha2("{$cargo}<br/>Ativos em  {$parametroAno}");
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Nível', 'Cargo', 'Perfil', 'Admissão', 'Saída']);
    $relatorio->set_align(["center", "left", "center", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe([null, null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_cargoSimples", "get_perfil"]);

    $relatorio->set_conteudo($result);

    # Seleciona o tipo de cargo
    $listaCargo = $servidor->select('SELECT distinct tipo,tipo from tbtipocargo');
    
    $relatorio->set_numGrupo(2);
    $relatorio->show();
    $page->terminaPagina();
}