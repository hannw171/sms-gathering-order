/// Model representing a guest Pharmacy / Apotek in the gathering event
class PharmacyModel {
  final String id;
  final String code;
  final String name;
  final String ownerName;
  final String city;
  final String address;
  final String tableNumber;
  final bool hasActiveDeviceSession;

  const PharmacyModel({
    required this.id,
    required this.code,
    required this.name,
    required this.ownerName,
    required this.city,
    required this.address,
    required this.tableNumber,
    this.hasActiveDeviceSession = false,
  });

  factory PharmacyModel.fromJson(Map<String, dynamic> json) {
    return PharmacyModel(
      id: json['id']?.toString() ?? '',
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      ownerName: json['owner_name']?.toString() ?? '',
      city: json['city']?.toString() ?? '',
      address: json['address']?.toString() ?? '',
      tableNumber: json['table_number']?.toString() ?? '-',
      hasActiveDeviceSession: json['has_active_session'] == true,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'code': code,
        'name': name,
        'owner_name': ownerName,
        'city': city,
        'address': address,
        'table_number': tableNumber,
        'has_active_session': hasActiveDeviceSession,
      };
}

