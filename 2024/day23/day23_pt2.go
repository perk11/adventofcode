package main

import (
	"fmt"
	"os"
	"slices"
	"sort"
	"strings"
)

type Connection struct {
	pc1 string
	pc2 string
}
type Computer struct {
	name                       string
	connectedComputers         []string
	fullyConnectedComputerSets [][]string
}

var computers map[string]Computer

func main() {
	filenames := []string{"testInput", "testInput2", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		connections := make([]Connection, len(lines))
		for index, line := range lines {
			connections[index] = Connection{line[0:2], line[3:]}
		}
		computers = make(map[string]Computer)
		for _, connection := range connections {
			pc1, ok := computers[connection.pc1]
			if ok {
				if !slices.Contains(computers[connection.pc1].connectedComputers, connection.pc2) {
					pc1.connectedComputers = append(pc1.connectedComputers, connection.pc2)
					computers[connection.pc1] = pc1
				}
			} else {
				computers[connection.pc1] = Computer{name: connection.pc1, connectedComputers: []string{connection.pc2}}
			}
			pc2, ok := computers[connection.pc2]
			if ok {
				if !slices.Contains(computers[connection.pc2].connectedComputers, connection.pc1) {
					pc2.connectedComputers = append(pc2.connectedComputers, connection.pc1)
					computers[connection.pc2] = pc2
				}
			} else {
				computers[connection.pc2] = Computer{name: connection.pc2, connectedComputers: []string{connection.pc1}}
			}
		}

		for index, computer := range computers {
			//println("Processing " + index)
			computer.fullyConnectedComputerSets = findFullyConnectedSets(computer.connectedComputers)
			computers[index] = computer
		}
		maxConnections := 0
		var mostConnectedSet []string
		for _, computer := range computers {
			for _, set := range computer.fullyConnectedComputerSets {
				if len(set) > maxConnections {
					maxConnections = len(set)
					mostConnectedSet = append(set, computer.name)
				}
			}
		}
		sort.Strings(mostConnectedSet)
		println(maxConnections)
		println(strings.Join(mostConnectedSet, ","))
	}
}
func findFullyConnectedSets(set []string) [][]string {
	if len(set) < 2 {
		return make([][]string, 0)
	}
	if isSetFullyConnected(set) {
		return [][]string{set}
	}
	sets := make([][]string, 0)
	for index := range set {
		excluded := make([]string, len(set))
		copy(excluded, set)
		excluded = append(excluded[:index], excluded[index+1:]...)
		fullyConnected := findFullyConnectedSets(excluded)
		if len(fullyConnected) > 0 {
			return fullyConnected
		}
	}
	slices.CompactFunc(sets, func(i []string, i2 []string) bool {
		for _, el := range i {
			if slices.Contains(i2, el) {
				return true
			}
		}
		return false
	})

	return sets
}
func isSetFullyConnected(set []string) bool {
	for _, computerName := range set {
		computer := computers[computerName]
		for _, computerName2 := range set {
			if computer.name == computerName2 {
				continue
			}
			if !slices.Contains(computer.connectedComputers, computerName2) {
				return false
			}
		}
	}
	return true
}
