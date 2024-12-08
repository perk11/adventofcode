package main

import (
	"fmt"
	"os"
	"strings"
)

var antennaPairs []AntennaPair
var maxX, maxY int

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		var antennasMapByFrequency = make(map[string][][]bool)
		antennaPairs = make([]AntennaPair, 0)
		lines := strings.Split(string(fileContents), "\n")
		maxX = len(lines[0]) - 1
		maxY = len(lines) - 1
		for lineIndex, line := range lines {
			frequencies := strings.Split(line, "")
			for frequencyIndex, frequency := range frequencies {
				if frequency == "." {
					continue
				}
				_, ok := antennasMapByFrequency[frequency]
				if !ok {
					antennasMapByFrequency[frequency] = make([][]bool, len(lines))
				}
				if antennasMapByFrequency[frequency][lineIndex] == nil {
					antennasMapByFrequency[frequency][lineIndex] = make([]bool, len(line))
				}
				antennasMapByFrequency[frequency][lineIndex][frequencyIndex] = true
			}
		}
		for _, thisFrequencyMap := range antennasMapByFrequency {
			for y := 0; y < len(thisFrequencyMap); y++ {
				for x := 0; x < len(thisFrequencyMap[y]); x++ {
					if !thisFrequencyMap[y][x] {
						continue
					}
					for y2 := 0; y2 < len(thisFrequencyMap); y2++ {
					pairSearch:
						for x2 := 0; x2 < len(thisFrequencyMap[y2]); x2++ {
							if y == y2 && x == x2 {
								continue
							}
							for _, pair := range antennaPairs {
								if pair.antenna1.x == x && pair.antenna1.y == y && pair.antenna2.x == x2 && pair.antenna2.y == y2 {
									continue pairSearch
								}
								if pair.antenna2.x == x && pair.antenna2.y == y && pair.antenna1.x == x2 && pair.antenna1.y == y2 {
									continue pairSearch
								}
							}
							if thisFrequencyMap[y2][x2] {
								antennaPairs = append(antennaPairs, AntennaPair{coordinates{x, y}, coordinates{x2, y2}})
							}
						}
					}
				}
			}
		}
		var antiMap = make([][]bool, len(lines))
		for y := 0; y < len(lines); y++ {
			antiMap[y] = make([]bool, len(lines[y]))
		}
		for _, pair := range antennaPairs {
			anti := antiForPair(pair)
			addAntennaIfWithinBounds(anti.antenna1, &antiMap)
			addAntennaIfWithinBounds(anti.antenna2, &antiMap)
		}
		var total = 0
		for y := 0; y <= maxY; y++ {
			for x := 0; x <= maxX; x++ {
				if antiMap[y][x] {
					total++
				}
			}
		}
		println(total)

	}

}
func addAntennaIfWithinBounds(antenna coordinates, antiMap *[][]bool) {
	if antenna.y > maxY || antenna.y < 0 {
		return
	}
	if antenna.x > maxX || antenna.x < 0 {
		return
	}

	(*antiMap)[antenna.y][antenna.x] = true
}
func antiForPair(pair AntennaPair) AntennaPair {
	xDistanceToAnti := pair.antenna2.x - pair.antenna1.x
	yDistanceToAnti := pair.antenna2.y - pair.antenna1.y
	point1 := coordinates{pair.antenna1.x + xDistanceToAnti, pair.antenna1.y + yDistanceToAnti}
	point2 := coordinates{pair.antenna1.x - xDistanceToAnti, pair.antenna1.y - yDistanceToAnti}
	point3 := coordinates{pair.antenna2.x + xDistanceToAnti, pair.antenna2.y + yDistanceToAnti}
	point4 := coordinates{pair.antenna2.x - xDistanceToAnti, pair.antenna2.y - yDistanceToAnti}
	result := AntennaPair{}
	if point1.x == pair.antenna2.x && point1.y == pair.antenna2.y {
		result.antenna1 = point2
	} else {
		result.antenna1 = point1
	}

	if point3.x == pair.antenna1.x && point3.y == pair.antenna1.y {
		result.antenna2 = point4
	} else {
		result.antenna2 = point3
	}

	return result
}

type coordinates struct {
	x, y int
}
type AntennaPair struct {
	antenna1, antenna2 coordinates
}
