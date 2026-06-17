class Order {
  final int id;
  final String customerName;
  final String status;
  final double totalAmount;

  Order({
    required this.id,
    required this.customerName,
    required this.status,
    required this.totalAmount,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      // On vérifie d'abord si 'client' existe et possède un 'name'
      customerName: (json['client'] != null && json['client']['name'] != null) 
          ? json['client']['name'] 
          : 'Client inconnu',
      status: json['status'] ?? 'En attente',
      // On utilise 'total_price' car c'est le nom de la colonne dans ta base de données
      totalAmount: (json['total_price'] != null) 
          ? double.tryParse(json['total_price'].toString()) ?? 0.0 
          : 0.0,
    );
  }
}