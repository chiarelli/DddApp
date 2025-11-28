# Exercise 3 — Fundamentos do Yii2

Branch: `exercise-3`

## Objetivos
- Listar todos os clientes e as pessoas vinculadas a eles.
- Exibir (cliente): Nome completo, Data de nascimento, Idade.
- Exibir (pessoa vinculada): Primeiro nome, Relação, Data de nascimento, Idade.

## Checklist

- [x] Domain
  - [x] VO Age com fromBirthdate(string|DateTimeInterface) e value(): int.
  - [x] Entidades Customer/Person com invariantes mínimas (nome não vazio, data válida).

- [x] Application
  - [x] DTOs CustomerDto, LinkedPersonDto, CustomerWithPeopleDto.
  - [x] UseCase ListCustomersWithPeopleQuery com execução stateless.

- [ ] Infra
  - [ ] Interface CustomerReadRepositoryInterface (application ou domain-ports).
  - [ ] Implementação YiiCustomerReadRepository usando AR/joins.
  - [ ] ARs e relations (se não existirem) CustomerAR::getPeople(), PersonAR::getCustomers().
  - [ ] Assemblers: mapeiam AR → Domain → DTO.

- [ ] Presentation
  - [ ] Controller CustomerController::actionIndex() injeta o UseCase via container.
  - [ ] View views/customer/index.php renderiza:
    - [ ] Blocos por cliente: Nome completo, Data de nascimento, Idade.
    - [ ] Tabela/lista por pessoa vinculada: Primeiro nome, Relação, Data de nascimento, Idade.
  - [ ] Links de navegação na navbar (opcional).

- [ ] Testes
  - [x] Unit para VO Age (datas limítrofes, anos bissextos).
  - [x] Executa listagem de pessoas relacionadas à customers.
  - [ ] Functional: acessar /customer/index e verificar estrutura/valores.
  - [ ] Integration: repository retornando agregados com relações corretas.


## Fundamentos

- UseCase de leitura isolado a um repositório de consulta (read model) facilita otimização sem acoplar o domínio a detalhes de AR/SQL.
- DTOs protegem a view de depender de entidades de domínio/infra; ajudam a manter a regra de idade centralizada.
- VO Age concentra lógica temporal, deixando controller e view simples.
- Relations AR na Infra viabilizam joins eficientes e baixo atrito com o Yii2.
