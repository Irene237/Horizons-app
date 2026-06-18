class Order {
  final int id;
  final String customerName;
  final double totalAmount;
  final String status;
  final String? filePath;

  Order({
    required this.id,
    required this.customerName,
    required this.totalAmount,
    required this.status,
    this.filePath,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    // Gestion sécurisée pour le totalAmount (au cas où il arrive en String ou null)
    final price = json['total_price'] != null 
        ? double.tryParse(json['total_price'].toString()) ?? 0.0 
        : 0.0;

    return Order(
      id: json['id'] ?? 0,
      customerName: (json['client'] != null && json['client']['name'] != null) 
          ? json['client']['name'] 
          : 'Inconnu',
      totalAmount: price,
      status: json['status'] ?? 'pending',
      // On récupère le chemin. Si l'API renvoie null, filePath sera null.
      filePath: json['file_path'], 
    );
  }
}